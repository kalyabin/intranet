<?php

namespace TicketBundle\Event;

use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление для пользователя о закрытии тикета
 *
 * @package TicketBundle\Event
 */
class TicketClosedNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    /**
     * Сформитьвать e-mail уведомление
     *
     * @param MailManager $mailManager
     *
     * @return null|\Swift_Message
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        /** @var TicketClosedEvent $parentEvent */
        $parentEvent = $this->getSubject();
        /** @var UserEntity $receiver */
        $receiver = $this->getArgument('receiver');

        $ticket = $parentEvent->getTicket();
        $subject = 'Заявка №' . $ticket->getNumber() . ': заявка закрыта';

        return $mailManager->buildMessageToUser($receiver, $subject, '@ticket_emails/closed_to_user.html.twig', [
            'ticket'   => $ticket,
            'category' => $ticket->getCategory()
        ]);
    }

    /**
     * Заполнить дополнительные поля для записи в БД
     *
     * @param UserNotificationEntity $notification
     *
     * @return UserNotificationEntity|null
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        /** @var TicketClosedEvent $parentEvent */
        $parentEvent = $this->getSubject();

        $notification
            ->setType(UserNotificationEntity::TYPE_TICKET_CLOSED)
            ->setTicket($parentEvent->getTicket());

        if ($parentEvent->getAuthor()) {
            $notification->setAuthor($parentEvent->getAuthor());
        }

        return $notification;
    }

    /**
     * Получатель сообщения
     *
     * @return UserEntity
     */
    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }
}
