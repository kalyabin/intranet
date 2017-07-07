<?php

namespace RentBundle\Event;

use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\EventDispatcher\Event;
use UserBundle\Entity\UserEntity;

/**
 * Событие об изменении заявки со стороны менеджера
 *
 * @package RentBundle\Event
 */
class RoomRequestUpdatedEvent extends Event
{
    const NAME = 'rent.room_request_updated';

    /**
     * @var RoomRequestEntity
     */
    protected $request;

    /**
     * @var UserEntity
     */
    protected $author;

    public function __construct(RoomRequestEntity $request, UserEntity $author)
    {
        $this->request = $request;
        $this->author = $author;
    }

    public function getRequest(): RoomRequestEntity
    {
        return $this->request;
    }

    public function getAuthor(): UserEntity
    {
        return $this->author;
    }
}
