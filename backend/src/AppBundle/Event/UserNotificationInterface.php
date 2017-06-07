<?php

namespace AppBundle\Event;

use AppBundle\Entity\UserNotificationEntity;
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
     * Билд e-mail, если требуется.
     *
     * Если не требуется отправка e-mail, должен возвращать null,
     *
     * @param MailManager $mailManager
     *
     * @return null|\Swift_Message
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message;

    /**
     * Конфигурирование дополнительных полей для записи уведомления в БД.
     *
     * Если не требуется запись в БД, должен возвращать null.
     *
     * @param UserNotificationEntity $notification
     *
     * @return UserNotificationEntity|null
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity;

    /**
     * Получатель уведомления
     *
     * @return UserEntity
     */
    public function getReceiver(): UserEntity;
}
