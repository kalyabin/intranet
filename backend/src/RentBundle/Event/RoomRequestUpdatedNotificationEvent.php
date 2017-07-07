<?php

namespace RentBundle\Event;

use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;


/**
 * Уведомление арендатору на редактирование заявки со стороны менеджера
 *
 * @package RentBundle\Event
 */
class RoomRequestUpdatedNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        $subject = 'Изменения по заявке на аренду помещения';
        $template = '@rent_emails/room_request_updated.html.twig';

        return $mailManager->buildMessageToUser($this->getReceiver(), $subject, $template, [
            'from' => $this->getRequest()->getFrom()->format('Y-m-d H:i'),
            'to' => $this->getRequest()->getTo()->format('Y-m-d H:i'),
            'customer' => $this->getRequest()->getCustomer(),
            'room' => $this->getRequest()->getRoom(),
            'status' => $this->getRequest()->getStatusName(),
            'managerComment' => $this->getRequest()->getManagerComment(),
        ]);
    }

    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_ROOM_REQUEST_UPDATED)
            ->setCustomer($this->getRequest()->getCustomer())
            ->setFrom($this->getRequest()->getFrom());

        return $notification;
    }

    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }

    public function getRequest(): RoomRequestEntity
    {
        return $this->getSubject();
    }
}
