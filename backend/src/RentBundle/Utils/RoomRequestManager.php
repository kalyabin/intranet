<?php

namespace RentBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Event\RoomRequestCancelledEvent;
use RentBundle\Event\RoomRequestCreatedEvent;
use RentBundle\Event\RoomRequestUpdatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UserBundle\Entity\UserEntity;


/**
 * Менеджер заявок для аренды помещений
 *
 * @package RentBundle\Utils
 */
class RoomRequestManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Создание заявки
     *
     * @param RoomRequestEntity $request
     * @param UserEntity $author Автор события
     */
    public function createRequest(RoomRequestEntity $request, UserEntity $author)
    {
        $request
            ->setCreatedAt(new \DateTime())
            ->setStatus(RoomRequestEntity::STATUS_PENDING);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $event = new RoomRequestCreatedEvent($request, $author);
        $this->eventDispatcher->dispatch(RoomRequestCreatedEvent::NAME, $event);
    }

    /**
     * Отмена заявки со стороны пользователя
     *
     * @param RoomRequestEntity $request
     * @param UserEntity $author Автор события
     */
    public function cancelRequest(RoomRequestEntity $request, UserEntity $author)
    {
        $request->setStatus(RoomRequestEntity::STATUS_CANCELED);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $event = new RoomRequestCancelledEvent($request, $author);
        $this->eventDispatcher->dispatch(RoomRequestCancelledEvent::NAME, $event);
    }

    /**
     * Обновление заявки со стороны менеджера
     *
     * @param RoomRequestEntity $request
     * @param UserEntity $author Автор события
     * @param string $oldStatus Предыдущий статус (для отправки уведомления об изменении статуса)
     */
    public function updateRequestByManager(RoomRequestEntity $request, UserEntity $author, ?string $oldStatus = null)
    {
        $this->entityManager->persist($request);
        $this->entityManager->flush();

        if (is_null($oldStatus) || $oldStatus != $request->getStatus()) {
            $event = new RoomRequestUpdatedEvent($request, $author);
            $this->eventDispatcher->dispatch(RoomRequestUpdatedEvent::NAME, $event);
        }
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
