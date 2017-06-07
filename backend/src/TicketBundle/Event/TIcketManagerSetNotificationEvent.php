<?php

namespace TicketBundle\Event;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о назначении менеджера
 *
 * @package TicketBundle\Event
 */
class TIcketManagerSetNotificationEvent extends GenericEvent implements UserNotificationInterface
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
        /** @var TicketManagerSetEvent $parentEvent */
        $parentEvent = $this->getSubject();
        /** @var UserEntity $receiver */
        $receiver = $this->getArgument('receiver');

        $ticket = $parentEvent->getTicket();
        $manager = $parentEvent->getManager();

        $subject = 'Заявка №' . $ticket->getNumber() . ': назначен менеджер';

        return $mailManager->buildMessageToUser($receiver, $subject, '@ticket_emails/set_manager_to_user.html.twig', [
            'ticket' => $ticket,
            'category' => $ticket->getCategory(),
            'manager' => $manager
        ]);
    }

    /**
     * Сконфигурировать уведомление для записи в БД
     *
     * @param UserNotificationEntity $notification
     *
     * @return UserNotificationEntity|null
     */
    public function configureNotification(UserNotificationEntity $notification): ?UserNotificationEntity
    {
        /** @var TicketManagerSetEvent $parentEvent */
        $parentEvent = $this->getSubject();

        return $notification
            ->setType(UserNotificationEntity::TYPE_TICKET_MANAGER_SET)
            ->setTicket($parentEvent->getTicket())
            ->setTicketManager($parentEvent->getManager());
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
