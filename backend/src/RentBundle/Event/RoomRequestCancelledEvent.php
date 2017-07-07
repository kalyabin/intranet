<?php

namespace RentBundle\Event;

use RentBundle\Entity\RoomRequestEntity;
use Symfony\Component\EventDispatcher\Event;
use UserBundle\Entity\UserEntity;

/**
 * Событие об отмене заявки на аренду помещения
 *
 * @package RentBundle\Event
 */
class RoomRequestCancelledEvent extends Event
{
    const NAME = 'rent.room_request_cancelled';

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
