<?php

namespace UserBundle\Controller\Response;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;

/**
 * JSON-ответ при авторизации пользователя
 *
 * В случае, если пользователь был заблокирован - отдает флаг isLocked.
 * В случае, если требуется активация - отдает флаг isNeedActivation и userId.
 * В случае любой другой ошибки отдает errorMessage.
 * Если успешно авторизован - отдает loggedIn.
 *
 * @package UserBundle\Controller\Response
 */
class AuthenticationJsonResponse extends JsonResponse implements ContainerAwareInterface
{
    /**
     * @var array JSON-данные для отдачи в браузер
     */
    protected $jsonData = [
        'loggedIn' => false,
        'isLocked' => false,
        'isNeedActivation' => false,
        'errorMessage' => '',
        'userId' => null,
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Зафиксировать успешность авторизации
     *
     * @param UsernamePasswordToken $token
     */
    public function handleSuccess(UsernamePasswordToken $token)
    {
        $this->jsonData['loggedIn'] = true;
        $this->jsonData['userId'] = $token->getUser()->getId();
        $this->setStatusCode(self::HTTP_OK);
        $this->setData($this->jsonData);
    }

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Получить пользователя из исключения
     *
     * @param AccountStatusException|AuthenticationException $exception
     *
     * @return null|UserEntity
     */
    protected function getUser($exception): ?UserEntity
    {
        if (!$exception instanceof AccountStatusException) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof AccountStatusException && $exception->getToken()) {
            $user = $exception->getToken()->getUser();

            if (is_string($user)) {
                /** @var UserRepository $repository */
                $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository(UserEntity::class);
                $user = $repository->findOneByEmail($user);
            }

            return $user instanceof UserEntity ? $user : null;
        }

        return null;
    }

    /**
     * Зафиксировать ошибку авторизации
     *
     * @param AuthenticationException|AccountStatusException $exception
     */
    public function handleFailure($exception)
    {
        $user = $this->getUser($exception);

        $jsonData = [
            'loggedIn' => false,
            'isLocked' => $user instanceof UserEntity ? $user->isLocked() : false,
            'isNeedActivation' => $user instanceof UserEntity ? $user->isNeedActivation() : false,
            'errorMessage' => $exception->getMessage(),
            'userId' => $user instanceof UserEntity ? $user->getId() : null,
        ];

        if ($jsonData['isLocked']) {
            $jsonData['errorMessage'] = 'Ваш аккаунт заблокирован';
        } else if ($jsonData['isNeedActivation']) {
            $jsonData['errorMessage'] = 'Требуется активация';
        } else {
            $jsonData['errorMessage'] = 'Неверный логин или пароль';
        }

        $this->jsonData = $jsonData;
        $this->setStatusCode(self::HTTP_UNAUTHORIZED);
        $this->setData($this->jsonData);
    }

    /**
     * Зафиксировать ошибочный запрос
     */
    public function handleFailRequest()
    {
        $this->setStatusCode(self::HTTP_BAD_REQUEST);
        $this->setData($this->jsonData);
    }
}
