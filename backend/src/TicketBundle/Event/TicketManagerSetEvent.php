<?php

namespace TicketBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TicketBundle\Entity\TicketEntity;
use UserBundle\Entity\UserEntity;

/**
 * Событие при назначении менеджера по тикету
 *
 * @package TicketBundle\Event
 */
class TicketManagerSetEvent extends Event
{
    const NAME = 'ticket.manager.set';

    /**
     * @var TicketEntity Тикет
     */
    protected $ticket;

    /**
     * @var UserEntity Назначенный менеджер
     */
    protected $manager;

    /**
     * TicketManagerSetEvent constructor.
     *
     * @param TicketEntity $ticket Тикет
     * @param UserEntity $manager Назначенный менеджер
     */
    public function __construct(TicketEntity $ticket, UserEntity $manager)
    {
        $this->ticket = $ticket;
        $this->manager = $manager;
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
    public function getManager(): UserEntity
    {
        return $this->manager;
    }
}
