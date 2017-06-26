<?php

namespace CustomerBundle\Utils;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Event\ServiceActivatedEvent;
use CustomerBundle\Event\ServiceActivatedNotificationEvent;
use CustomerBundle\Event\ServiceDeactivatedEvent;
use CustomerBundle\Event\ServiceDeactivatedNotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Сервис нотификаций об активированных или деактивированных услугах
 *
 * @package CustomerBundle\Utils
 */
class ServiceNotificationManager implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            ServiceActivatedEvent::NAME => 'onServiceActivated',
            ServiceDeactivatedEvent::NAME => 'onServiceDeactivated',
        ];
    }

    /**
     * Получить пользователей арендатора, которые должны получать уведомления об изменениях в услугах
     *
     * @param CustomerEntity $customer
     *
     * @return UserEntity[]
     */
    protected function getCustomerReceivers(CustomerEntity $customer): array
    {
        // получить родительские роли для пользователей управляющих услугами
        $roles = $this->rolesManager->getParentRoles('ROLE_SERVICE_CUSTOMER');
        $result = [];
        foreach ($this->userRepository->findByRoleAndCustomer($roles, $customer) as $batch) {
            foreach ($batch as $receiver) {
                $result[] = $receiver;
            }
        }
        return $result;
    }

    /**
     * Подписка на активацию услуги
     *
     * @param ServiceActivatedEvent $event
     */
    public function onServiceActivated(ServiceActivatedEvent $event)
    {
        foreach ($this->getCustomerReceivers($event->getCustomer()) as $receiver) {
            $notification = new ServiceActivatedNotificationEvent(null, [
                'receiver' => $receiver,
                'service' => $event->getService(),
                'tariff' => $event->getTariff(),
            ]);
            $this->eventDispatcher->dispatch('user_notification', $notification);
        }
    }

    /**
     * Подписка на деактивацию услуги
     *
     * @param ServiceDeactivatedEvent $event
     */
    public function onServiceDeactivated(ServiceDeactivatedEvent $event)
    {
        foreach ($this->getCustomerReceivers($event->getCustomer()) as $receiver) {
            $notification = new ServiceDeactivatedNotificationEvent(null, [
                'receiver' => $receiver,
                'service' => $event->getService()
            ]);
            $this->eventDispatcher->dispatch('user_notification', $notification);
        }
    }
}
