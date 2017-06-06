<?php

namespace UserBundle\Utils;

use AppBundle\Utils\MailManager;
use UserBundle\Entity\UserCheckerEntity;
use UserBundle\Entity\UserEntity;
use UserBundle\Event\UserChangedPasswordEvent;
use UserBundle\Event\UserChangeEmailEvent;
use UserBundle\Event\UserCreationEvent;
use UserBundle\Event\UserRegistrationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use UserBundle\Event\UserRememberPasswordEvent;

/**
 * Мейлер для системных уведомлений пользователю:
 * - подтверждение емейла, восстановление пароля и тому подобное.
 *
 * @package UserBundle\Utils
 */
class UserSystemMailManager implements EventSubscriberInterface
{
    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * Конструктор
     *
     * @param MailManager $mailManager Системный отправитель в приложении
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * Подписка на события
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserRegistrationEvent::NAME => 'onUserRegistration',
            UserRememberPasswordEvent::NAME => 'onUserRememberPassword',
            UserChangedPasswordEvent::NAME => 'onUserChangePassword',
            UserChangeEmailEvent::NAME => 'onUserChangeEmail',
            UserCreationEvent::NAME => 'onUserCreation'
        ];
    }

    /**
     * Отправить пользователю приветственное письмо после создания аккаунта
     *
     * @param UserEntity $user Модель зарегистрированного пользователя
     * @param string $password Пользовательский пароль
     *
     * @return integer
     */
    public function sendWelcomeEmail(UserEntity $user, string $password)
    {
        $message = $this->mailManager->buildMessageToUser($user, 'Создан аккаунт', '@user_emails/welcome.html.twig', [
            'user' => $user,
            'password' => $password
        ]);

        return $this->mailManager->sendMessage($message);
    }

    /**
     * Отправить письмо об активации аккаунта пользователю
     *
     * @param UserEntity $user Модель зарегистрированного пользователя
     * @param UserCheckerEntity $checker Модель кода подтверждения
     *
     * @return integer
     */
    public function sendActivationEmail(UserEntity $user, UserCheckerEntity $checker)
    {
        $message = $this->mailManager->buildMessageToUser($user, 'Активация аккаунта', '@user_emails/registration.html.twig', [
            'user' => $user,
            'checker' => $checker,
        ]);

        return $this->mailManager->sendMessage($message);
    }

    /**
     * Отправить запрос на изменение e-mail в аккаунте.
     *
     * @param UserEntity $user Модель пользователя, для которого меняется e-mail
     * @param UserCheckerEntity $checker Модель кода подтверждения
     * @param string $newEmail Новый e-mail, на который отправить код подтверждения
     *
     * @return int
     */
    public function sendChangeEmailConfirmation(UserEntity $user, UserCheckerEntity $checker, $newEmail)
    {
        $message = $this->mailManager->buildMessageToUser($user, 'Смена e-mail', '@user_emails/change_email.html.twig', [
            'user' => $user,
            'checker' => $checker,
            'newEmail' => $newEmail,
        ]);

        return $this->mailManager->sendMessage($message);
    }

    /**
     * Отправить письмо о восстановлении пароля
     *
     * @param UserEntity        $user Модель пользователя
     * @param UserCheckerEntity $checker Модель кода подтверждения
     *
     * @return int
     */
    public function sendRememberPasswordEmail(UserEntity $user, UserCheckerEntity $checker)
    {
        $message = $this->mailManager->buildMessageToUser($user, 'Восстановление пароля', '@user_emails/remember_password.html.twig', [
            'user' => $user,
            'checker' => $checker,
        ]);

        return $this->mailManager->sendMessage($message);
    }

    /**
     * Отправить письмо о том, что установлен новый пароль
     *
     * @param UserEntity $user Модель пользователя
     * @param string $newPassword Новый установленный пароль
     *
     * @return int
     */
    public function sendNewPassword(UserEntity $user, $newPassword)
    {
        $message = $this->mailManager->buildMessageToUser($user, 'Установлен новый пароль', '@user_emails/set_new_password.html.twig', [
            'user' => $user,
            'newPassword' => $newPassword
        ]);

        return $this->mailManager->sendMessage($message);
    }

    /**
     * Подписка на событие при создании пользователя через админку
     *
     * @param UserCreationEvent $event
     */
    public function onUserCreation(UserCreationEvent $event)
    {
        $this->sendWelcomeEmail($event->getUser(), $event->getPassword());
    }

    /**
     * Подписка на событие о регистрации пользователя
     *
     * @param UserRegistrationEvent $event
     */
    public function onUserRegistration(UserRegistrationEvent $event)
    {
        $this->sendActivationEmail($event->getUser(), $event->getChecker());
    }

    /**
     * Подписка на событие о восстановлении пароля
     *
     * @param UserRememberPasswordEvent $event
     */
    public function onUserRememberPassword(UserRememberPasswordEvent $event)
    {
        $this->sendRememberPasswordEmail($event->getUser(), $event->getChecker());
    }

    /**
     * Подписка на событие об изменении пароля
     *
     * @param UserChangedPasswordEvent $event
     */
    public function onUserChangePassword(UserChangedPasswordEvent $event)
    {
        $this->sendNewPassword($event->getUser(), $event->getNewPassword());
    }

    /**
     * Подписка на событие об изменении e-mail
     *
     * @param UserChangeEmailEvent $event
     */
    public function onUserChangeEmail(UserChangeEmailEvent $event)
    {
        $this->sendChangeEmailConfirmation($event->getUser(), $event->getChecker(), $event->getNewEmail());
    }
}
