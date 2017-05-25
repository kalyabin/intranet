<?php

namespace TicketBundle\Utils;


use Symfony\Component\Templating\EngineInterface;
use TicketBundle\Event\TicketNewEvent;

/**
 * Мейлер для событий тикетной системы
 *
 * @package TicketBundle\Utils
 */
class TicketMailManager
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var \Swift_Message Последнее отправленное сообщение
     */
    protected $lastMessage;

    /**
     * TicketMailManager constructor.
     *
     * @param \Swift_Mailer $mailer Мейлер для отправки почты
     * @param EngineInterface $templating Движок для шаблонизации twig
     * @param null|string $from E-mail отправителя (по умолчанию - без отправителя)
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, ?string $from = null)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->from = $from;
    }

    /**
     * Установка отправителя
     *
     * @param string $from
     *
     * @return TicketMailManager
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public static function getSubscribedEvents()
    {
        return [
            TicketNewEvent::NEW_TICKET => 'onNewTicket',
            TicketNewEvent::NEW_ANSWER => 'onNewAnswer',
            TicketNewEvent::NEW_QUESTION => 'onNewQuestion',
        ];
    }

    /**
     * Отправка уже сформированного письма
     *
     * @param \Swift_Message $message Сообщение с сабжектом и телом
     * @param string $email E-mail, на который надо отправить письмо
     *
     * @return int
     */
    protected function sendMessage(\Swift_Message $message, $email)
    {
        $message
            ->setFrom($this->from)
            ->setTo($email);

        $this->lastMessage = $message;

        return $this->mailer->send($message);
    }

    /**
     * Получить последнее сообщение
     *
     * @return null|\Swift_Message
     */
    public function getLastMessage(): ?\Swift_Message
    {
        return $this->lastMessage;
    }

    /**
     * Стереть последнее сообщение
     */
    public function clearLastMessage()
    {
        $this->lastMessage = null;
    }

    /**
     * Событие на создание нового тикета
     *
     * @param TicketNewEvent $event
     */
    public function onNewTicket(TicketNewEvent $event)
    {
        // TODO: реализовать логику
    }

    /**
     * Событие на создание нового ответа по тикету
     *
     * @param TicketNewEvent $event
     */
    public function onNewAnswer(TicketNewEvent $event)
    {
        // TODO: реализовать логику
    }

    /**
     * Событие на создание нового вопроса по тикету
     *
     * @param TicketNewEvent $event
     */
    public function onNewQuestion(TicketNewEvent $event)
    {
        // TODO: реализовать логику
    }
}
