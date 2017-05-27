<?php

namespace TicketBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TicketBundle\Entity\TicketEntity;
use UserBundle\Entity\UserEntity;

/**
 * Событие при закрытии тикета
 *
 * @package TicketBundle\Event
 */
class TicketClosedEvent extends Event
{
    const NAME = 'ticket.closed';

    /**
     * @var TicketEntity Тикет
     */
    protected $ticket;

    /**
     * @var UserEntity Автор события
     */
    protected $author;

    /**
     * TicketClosedEvent constructor.
     *
     * @param TicketEntity $ticket Тикет
     * @param UserEntity $author Автор события
     */
    public function __construct(TicketEntity $ticket, UserEntity $author)
    {
        $this->ticket = $ticket;
        $this->author = $author;
    }

    /**
     * @return TicketEntity
     */
    public function getTicket(): TicketEntity
    {
        return $this->ticket;
    }

    /**
     * @return UserEntity
     */
    public function getAuthor(): UserEntity
    {
        return $this->author;
    }
}
