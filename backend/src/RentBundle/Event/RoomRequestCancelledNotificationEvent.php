<?php

namespace RentBundle\Event;

use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о закрытии заявки
 *
 * @package RentBundle\Event
 */
class RoomRequestCancelledNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        $subject = 'Заявка на бронирование помещения отменена';
        $template = '@rent_emails/room_request_cancelled.html.twig';

        return $mailManager->buildMessageToUser($this->getReceiver(), $subject, $template, [
            'from' => $this->getRequest()->getFrom()->format('Y-m-d H:i'),
            'to' => $this->getRequest()->getTo()->format('Y-m-d H:i'),
            'customer' => $this->getRequest()->getCustomer(),
            'room' => $this->getRequest()->getRoom(),
        ]);
    }

    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_ROOM_REQUEST_CANCELLED)
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
