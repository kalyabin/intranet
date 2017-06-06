<?php

namespace AppBundle\Event;

use AppBundle\Utils\MailManager;
use UserBundle\Entity\UserEntity;

/**
 * Интерфейс системного сообщения пользователю
 *
 * @package AppBundle\EventDispatcher
 */
interface UserNotificationInterface
{
    /**
     * Билд e-mail, если требуется
     *
     * @param MailManager $mailManager
     *
     * @return null|\Swift_Message
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message;

    /**
     * Получатель уведомления
     *
     * @return UserEntity
     */
    public function getReceiver(): UserEntity;
}
