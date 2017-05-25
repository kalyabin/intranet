<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 25.05.17
 * Time: 21:59
 */

namespace TicketBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;

/**
 * Событие на создание нового сообщения в тикете
 *
 * @package TicketBundle\Event
 */
class TicketNewMessageEvent extends Event
{
    /**
     * Название события для нового ответа по тикету
     */
    const NEW_ANSWER = 'ticket.new_answer';

    /**
     * Название события для нового вопроса по тикету
     */
    const NEW_QUESTION = 'ticket.new_question';

    /**
     * @var TicketEntity
     */
    protected $ticket;

    /**
     * @var TicketMessageEntity
     */
    protected $message;

    /**
     * TicketNewMessageEvent constructor.
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
