<?php

namespace TicketBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;

/**
 * Событие на создание нового тикета
 *
 * @package TicketBundle\Event
 */
class TicketNewEvent extends Event
{
    /**
     * Название события для нового тикета
     */
    const NAME = 'ticket.new';

    /**
     * @var TicketEntity
     */
    protected $ticket;

    /**
     * @var TicketMessageEntity
     */
    protected $message;

    /**
     * TicketNewEvent constructor.
     *
     * @param TicketEntity $ticket
     * @param TicketMessageEntity $message
     */
    public function __construct(TicketEntity $ticket, TicketMessageEntity $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    /**
     * Получить заявку
     *
     * @return TicketEntity
     */
    public function getTicket(): TicketEntity
    {
        return $this->ticket;
    }

    /**
     * Получить первое сообщение по тикету
     *
     * @return TicketMessageEntity
     */
    public function getMessage(): TicketMessageEntity
    {
        return $this->message;
    }
}
