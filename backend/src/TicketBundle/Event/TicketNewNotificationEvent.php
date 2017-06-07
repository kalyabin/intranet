<?php

namespace TicketBundle\Event;


use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о новом тикете
 *
 * @package TicketBundle\Event
 */
class TicketNewNotificationEvent extends GenericEvent implements UserNotificationInterface
{
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        /** @var TicketNewEvent $parentEvent */
        $parentEvent = $this->getSubject();
        /** @var UserEntity $receiver */
        $receiver = $this->getArgument('receiver');

        // отправка уведомления о новом тикете
        // либо арендатору, либо менеджеру
        $ticket = $parentEvent->getTicket();
        $category = $ticket->getCategory();
        $ticketMessage = $parentEvent->getMessage();

        if ($ticket->getCreatedBy()->getId() == $receiver->getId()) {
            // отправка арендатору
            $subject = 'Заявка №' . $ticket->getNumber() . ' зарегистрирована в системе';
            $template = '@ticket_emails/new_ticket_to_user.html.twig';
        } else {
            // отправка менеджеру
            $subject = $category->getName() . ': Получена новая заявка №' . $ticket->getNumber();
            $template = '@ticket_emails/new_ticket_to_manager.html.twig';
        }

        return $mailManager->buildMessageToUser($receiver, $subject, $template, [
            'ticket' => $ticket,
            'category' => $category,
            'message' => $ticketMessage,
        ]);
    }

    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }
}
