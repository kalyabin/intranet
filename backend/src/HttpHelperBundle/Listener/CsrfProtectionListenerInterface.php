<?php

namespace HttpHelperBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Интерфейс для проверки токенов CSRF защиты
 *
 * @package HttpHelperBundle\Listener
 */
interface CsrfProtectionListenerInterface
{
    /**
     * Получить название класса аннотаций для отключения проверки по токену
     *
     * @return string
     */
    public function getDisabledAnnotationClass();

    /**
     * Возвращает true, если внутри метода контроллера отключена проверка токенов
     *
     * @param \ReflectionMethod $method
     *
     * @return bool
     */
    public function isDisabledMethod(\ReflectionMethod $method);

    /**
     * Возвращает true, если внутри класса контроллера отключена проверка токенов
     *
     * @param \ReflectionClass $class
     *
     * @return bool
     */
    public function isDisabledClass(\ReflectionClass $class);

    /**
     * Проверка токена в HTTP-запросе или прослушивание аннотаций внутри контроллера, в случае если проверка отключена
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event);

    /**
     * Возвращает true, если токен был передан и он валиден.
     *
     * @return boolean
     */
    public function isTokenValid();
}
