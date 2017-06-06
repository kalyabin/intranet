<?php

namespace TicketBundle\Event;

use AppBundle\Event\UserNotificationInterface;
use AppBundle\Utils\MailManager;
use Symfony\Component\EventDispatcher\Event;
use UserBundle\Entity\UserEntity;

/**
 * Создание системного уведомления для пользователя на основе события в тикетной системе
 *
 * @package TicketBundle\Event
 */
class TicketUserNotificationEvent extends Event implements UserNotificationInterface
{
    /**
     * @var Event
     */
    protected $parentEvent;

    /**
     * @var UserEntity
     */
    protected $receiver;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * TicketUserEvent constructor.
     *
     * @param string $eventName Название события
     * @param Event $parentEvent Родительское событие в тикетной системе, на основе которого создать уведомление пользователю
     * @param UserEntity $receiver Получатель уведомления
     */
    public function __construct(string $eventName, Event $parentEvent, UserEntity $receiver)
    {
        $this->eventName = $eventName;
        $this->parentEvent = $parentEvent;
        $this->receiver = $receiver;
    }

    /**
     * Сфомировать e-mail уведомление для события
     *
     * @param MailManager $mailManager
     *
     * @return null|\Swift_Message
     */
    public function buildMailMessage(MailManager $mailManager): ?\Swift_Message
    {
        if ($this->parentEvent instanceof TicketClosedEvent) {
            // отправка уведомления о закрытии тикета
            $ticket = $this->parentEvent->getTicket();
            $subject = 'Заявка №' . $ticket->getNumber() . ': заявка закрыта';

            return $mailManager->buildMessageToUser($this->receiver, $subject, '@ticket_emails/closed_to_user.html.twig', [
                'ticket'   => $ticket,
                'category' => $ticket->getCategory()
            ]);
        } elseif ($this->parentEvent instanceof TicketManagerSetEvent) {
            // отправка уведомления о назначении менеджера по тикету
            $ticket = $this->parentEvent->getTicket();
            $manager = $this->parentEvent->getManager();
            $subject = 'Заявка №' . $ticket->getNumber() . ': назначен менеджер';

            return $mailManager->buildMessageToUser($this->receiver, $subject, '@ticket_emails/set_manager_to_user.html.twig', [
                'ticket' => $ticket,
                'category' => $ticket->getCategory(),
                'manager' => $manager
            ]);
        } elseif ($this->parentEvent instanceof TicketNewMessageEvent) {
            // отправка уведомления о новом сообщении
            // либо арендатору, либо менеджеру
            $ticket = $this->parentEvent->getTicket();
            $ticketMessage = $this->parentEvent->getMessage();
            $category = $ticket->getCategory();

            if ($this->eventName == TicketNewMessageEvent::NEW_QUESTION) {
                // отправка менеджеру
                $subject = $category->getName() . ': поступил новый вопрос по заявке №' . $ticket->getNumber();
                $template = '@ticket_emails/new_question_to_manager.html.twig';
            } else {
                // отправка арендатору
                $subject = 'Заявка №' . $ticket->getNumber() . ': поступил новый ответ';
                $template = '@ticket_emails/new_answer_to_user.html.twig';
            }

            return $mailManager->buildMessageToUser($this->receiver, $subject, $template, [
                'ticket' => $ticket,
                'category' => $ticket->getCategory(),
                'message' => $ticketMessage
            ]);
        } elseif ($this->parentEvent instanceof TicketNewEvent) {
            // отправка уведомления о новом тикете
            // либо арендатору, либо менеджеру
            $ticket = $this->parentEvent->getTicket();
            $category = $ticket->getCategory();
            $ticketMessage = $this->parentEvent->getMessage();

            if ($ticket->getCreatedBy()->getId() == $this->receiver->getId()) {
                // отправка арендатору
                $subject = 'Заявка №' . $ticket->getNumber() . ' зарегистрирована в системе';
                $template = '@ticket_emails/new_ticket_to_user.html.twig';
            } else {
                // отправка менеджеру
                $subject = $category->getName() . ': Получена новая заявка №' . $ticket->getNumber();
                $template = '@ticket_emails/new_ticket_to_manager.html.twig';
            }

            return $mailManager->buildMessageToUser($this->receiver, $subject, $template, [
                'ticket' => $ticket,
                'category' => $category,
                'message' => $ticketMessage,
            ]);
        } else {
            throw new \InvalidArgumentException('Invalid parent event type');
        }
    }

    public function getReceiver(): UserEntity
    {
        return $this->receiver;
    }
}
