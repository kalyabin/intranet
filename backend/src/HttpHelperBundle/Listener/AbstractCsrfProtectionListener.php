<?php

namespace HttpHelperBundle\Listener;


use Doctrine\Common\Annotations\Reader;
use HttpHelperBundle\Annotation\DisableCsrfProtection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Абстрактный класс для проверки CSRF-токенов в HTTP-запросе
 *
 * После каждого не-GET запроса генерирует новый X-CSRF-Token и вставляет его в ответ.
 *
 * Подписывается на событие onKernelController и onKernelResponse.
 *
 * Если в контроллере есть аннотация, отключающая проверку CSRF - ничего не делает.
 * Если проверка на токен не пройдена - генерирует исключение.
 *
 * @package HttpHelperBundle\Listener
 */
abstract class AbstractCsrfProtectionListener implements EventSubscriberInterface, CsrfProtectionListenerInterface
{

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string Заголовок, в котором ожидается токен
     */
    protected $tokenHeader = 'X-CSRF-Token';

    /**
     * @var string Идентификатор токена, по которму будет сгенерирован сам токен
     */
    protected $tokenIntention;

    /**
     * @var CsrfTokenManagerInterface Менеджер токенов CSRF
     */
    protected $tokenProvider;

    /**
     * @var Reader Прослушиватель аннотаций
     */
    protected $annotationReader;

    /**
     * Конструктор
     *
     * @param RequestStack $requestStack HTTP-запрос
     * @param CsrfTokenManagerInterface $tokenProvider Провайдер токенов
     * @param Reader $annotationReader Прослушиватель аннотаций
     * @param string $tokenIntention Идентификатор токена
     * @param string $tokenHeader Заголовок, в котором ожидается токен
     */
    public function __construct(RequestStack $requestStack, CsrfTokenManagerInterface $tokenProvider, Reader $annotationReader, $tokenIntention, $tokenHeader = 'X-CSRF-Token')
    {
        $this->tokenIntention = $tokenIntention;
        $this->tokenHeader = $tokenHeader;
        $this->tokenProvider = $tokenProvider;
        $this->annotationReader = $annotationReader;
        $this->requestStack = $requestStack;
    }

    /**
     * Подписка на события
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * Получить название класса аннотаций для отключения проверки по токену
     *
     * @return string
     */
    public function getDisabledAnnotationClass()
    {
        return DisableCsrfProtection::class;
    }

    /**
     * Проверка токена в HTTP-запросе или прослушивание аннотаций внутри контроллера, в случае если проверка отключена
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        list($controller, $action) = $event->getController();

        // если пришел GET-запрос, то ничего не делаем
        if (strtoupper($this->requestStack->getMasterRequest()->getMethod()) === 'GET') {
            return;
        }

        $class = new \ReflectionObject($controller);
        if ($this->isDisabledClass($class)) {
            return;
        }

        $method = new \ReflectionMethod($controller, $action);
        if ($this->isDisabledMethod($method)) {
            return;
        }

        if (!$this->isTokenValid()) {
            throw new BadRequestHttpException('Token is invalid');
        }
    }

    /**
     * Если пришел запрос неGET (POST, PUT, HEAD), то вставляет в заголовок обновленный X-CSRF-Token.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (strtoupper($event->getRequest()->getMethod()) !== 'GET') {
            $this->tokenProvider->removeToken($this->tokenIntention);
            $newToken = $this->tokenProvider->getToken($this->tokenIntention);
            $event->getResponse()->headers->set($this->tokenHeader, $newToken->getValue());
        }
    }
}
