<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 08.07.17
 * Time: 0:12
 */

namespace RentBundle\Event;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о создании заявки
 *
 * @package RentBundle\Event
 */
class RoomRequestCreatedNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        $subject = 'Создана заявка на бронирование помещения';
        $template = '@rent_emails/room_request_created.html.twig';

        return $mailManager->buildMessageToUser($this->getReceiver(), $subject, $template, [
            'from' => $this->getRequest()->getFrom()->format('d.m.Y H:i'),
            'to' => $this->getRequest()->getTo()->format('d.m.Y H:i'),
            'customer' => $this->getRequest()->getCustomer(),
            'room' => $this->getRequest()->getRoom(),
        ]);
    }

    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        $notification
            ->setType(UserNotificationEntity::TYPE_ROOM_REQUEST_CREATED)
            ->setRoom($this->getRequest()->getRoom())
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
