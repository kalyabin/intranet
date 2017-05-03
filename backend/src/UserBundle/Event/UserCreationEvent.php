<?php

namespace UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use UserBundle\Entity\UserEntity;


/**
 * Событие на создание пользователя через админку
 *
 * @package UserBundle\Event
 */
class UserCreationEvent extends Event
{
    /**
     * Название события
     */
    const NAME = 'user.creation';

    /**
     * @var UserEntity Модель нового пользователя
     */
    protected $user;

    /**
     * @var string Пароль нового пользователя
     */
    protected $password;

    public function __construct(UserEntity $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Получить пользователя
     *
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * Получить пароль
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
