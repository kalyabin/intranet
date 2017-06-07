<?php

namespace TicketBundle\Event;


use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use TicketBundle\Entity\TicketMessageEntity;
use UserBundle\Entity\UserEntity;

/**
 * Уведомление о новом сообщении - ответе или вопросе
 *
 * @package TicketBundle\Event
 */
class TicketNewMessageNotificationEvent extends GenericEvent implements UserNotificationInterface
{

    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        /** @var TicketNewMessageEvent $parentEvent */
        $parentEvent = $this->getSubject();
        /** @var UserEntity $receiver */
        $receiver = $this->getArgument('receiver');

        // отправка уведомления о новом сообщении
        // либо арендатору, либо менеджеру
        $ticket = $parentEvent->getTicket();
        $ticketMessage = $parentEvent->getMessage();
        $category = $ticket->getCategory();

        if ($ticketMessage->getType() == TicketMessageEntity::TYPE_QUESTION) {
            // отправка менеджеру
            $subject = $category->getName() . ': поступил новый вопрос по заявке №' . $ticket->getNumber();
            $template = '@ticket_emails/new_question_to_manager.html.twig';
        } else {
            // отправка арендатору
            $subject = 'Заявка №' . $ticket->getNumber() . ': поступил новый ответ';
            $template = '@ticket_emails/new_answer_to_user.html.twig';
        }

        return $mailManager->buildMessageToUser($receiver, $subject, $template, [
            'ticket' => $ticket,
            'category' => $ticket->getCategory(),
            'message' => $ticketMessage
        ]);
    }

    public function getReceiver(): UserEntity
    {
        return $this->getArgument('receiver');
    }
}
