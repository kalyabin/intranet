<?php

namespace AppBundle\Utils;
use AppBundle\Event\UserNotificationInterface;


/**
 * Менеджер системных уведомлений для пользователя
 *
 * @package AppBundle\Utils
 */
class UserNotificationManager
{
    /**
     * @var MailManager
     */
    protected $mailManager;

    /**
     * Установить мейлер для отправки писем при создании уведомлений
     *
     * @param MailManager $mailManager
     */
    public function setMailManager(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * Если установлен мейлер - отправить системное уведомление по почте
     *
     * @param UserNotificationInterface $event
     */
    protected function sendMailMessage(UserNotificationInterface $event)
    {
        $receiver = $event->getReceiver();

        if ($this->mailManager) {
            $message = $event->buildMailMessage($this->mailManager);

            if ($message) {
                $message->setTo([
                    $receiver->getEmail() => $receiver->getName()
                ]);

                $this->mailManager->sendMessage($message);
            }
        }
    }

    /**
     * Обработка уведомления пользователю
     *
     * @param UserNotificationInterface $event
     */
    public function onUserNotification(UserNotificationInterface $event)
    {
        // отправка уведомления по e-mail
        $this->sendMailMessage($event);
    }
}
