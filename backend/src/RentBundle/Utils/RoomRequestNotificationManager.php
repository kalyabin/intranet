<?php

namespace RentBundle\Utils;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityManagerInterface;
use RentBundle\Event\RoomRequestCancelledEvent;
use RentBundle\Event\RoomRequestCancelledNotificationEvent;
use RentBundle\Event\RoomRequestCreatedEvent;
use RentBundle\Event\RoomRequestCreatedNotificationEvent;
use RentBundle\Event\RoomRequestUpdatedEvent;
use RentBundle\Event\RoomRequestUpdatedNotificationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Система уведомлений для модуля аренды помещений
 *
 * @package RentBundle\Utils
 */
class RoomRequestNotificationManager implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var RolesManager
     */
    protected $rolesManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(EntityManagerInterface $entityManager, RolesManager $rolesManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->rolesManager = $rolesManager;
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Получить пользователей арендатора, которые имеют доступ к модулю аренды
     *
     * @param CustomerEntity $customer
     *
     * @return UserEntity[]
     */
    protected function getCustomerReceivers(CustomerEntity $customer): array
    {
        $roles = $this->rolesManager->getParentRoles('ROLE_RENT_CUSTOMER');
        $result = [];
        foreach ($this->userRepository->findByRoleAndCustomer($roles, $customer) as $batch) {
            foreach ($batch as $receiver) {
                $result[] = $receiver;
            }
        }
        return $result;
    }

    /**
     * Получить пользователей-менеджеров, имеющих право отвечать за модуль аренды
     *
     * @return UserEntity[]
     */
    protected function getManagerReceivers(): array
    {
        $roles = $this->rolesManager->getParentRoles('ROLE_RENT_MANAGEMENT');
        $result = [];
        foreach ($this->userRepository->findByRole($roles) as $batch) {
            foreach ($batch as $receiver) {
                $result[] = $receiver;
            }
        }
        return $result;

    }

    /**
     * При создании заявки отправлять уведомления менеджерам
     *
     * @param RoomRequestCreatedEvent $event
     */
    public function onRequestCreated(RoomRequestCreatedEvent $event)
    {
        foreach ($this->getManagerReceivers() as $receiver) {
            $newEvent = new RoomRequestCreatedNotificationEvent($event->getRequest(), [
                'receiver' => $receiver
            ]);
            $this->eventDispatcher->dispatch('user_notification', $newEvent);
        }
    }

    /**
     * При отмене заявки отправлять уведомления менеджерам
     *
     * @param RoomRequestCancelledEvent $event
     */
    public function onRequestCancelled(RoomRequestCancelledEvent $event)
    {
        foreach ($this->getManagerReceivers() as $receiver) {
            $newEvent = new RoomRequestCancelledNotificationEvent($event->getRequest(), [
                'receiver' => $receiver
            ]);
            $this->eventDispatcher->dispatch('user_notification', $newEvent);
        }
    }

    /**
     * При редактировании заявки менеджером отправлять уведомление арендаторам
     *
     * @param RoomRequestUpdatedEvent $event
     */
    public function onRequestUpdated(RoomRequestUpdatedEvent $event)
    {
        foreach ($this->getCustomerReceivers($event->getRequest()->getCustomer()) as $receiver) {
            $newEvent = new RoomRequestUpdatedNotificationEvent($event->getRequest(), [
                'receiver' => $receiver
            ]);
            $this->eventDispatcher->dispatch('user_notification', $newEvent);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            RoomRequestCreatedEvent::NAME => 'onRequestCreated',
            RoomRequestCancelledEvent::NAME => 'onRequestCancelled',
            RoomRequestUpdatedEvent::NAME => 'onRequestUpdated',
        ];
    }
}
