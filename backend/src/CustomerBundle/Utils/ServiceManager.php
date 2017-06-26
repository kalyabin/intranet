<?php

namespace CustomerBundle\Utils;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\ServiceHistoryRepository;
use CustomerBundle\Entity\ServiceActivatedEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use CustomerBundle\Event\ServiceActivatedEvent;
use CustomerBundle\Event\ServiceDeactivatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Управление услугами: подключение или отключение услуг арендатора
 *
 * @package CustomerBundle\Utils
 */
class ServiceManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ServiceHistoryRepository
     */
    protected $serviceHistoryRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->serviceHistoryRepository = $entityManager->getRepository(ServiceHistoryEntity::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Возвращает true, если услуга уже подключена к договору арендатора
     *
     * @param CustomerEntity $customer Арендатор, в котором проверить подключенные услуги
     * @param ServiceEntity $service Услуга для проверки
     *
     * @return bool
     */
    public function serviceIsAssigned(CustomerEntity $customer, ServiceEntity $service): bool
    {
        foreach ($customer->getService() as $assignedService) {
            /** @var ServiceActivatedEntity $assignedService */
            if ($assignedService->getService()->getId() == $service->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Подключение дополнительной услуги к договору арендатора.
     *
     * Если услуга уже активирована возвращает false.
     *
     * @param CustomerEntity $customer Арендатор, для которого подключить услугу
     * @param ServiceEntity $service Подключаемая услуга
     * @param ServiceTariffEntity|null $tariff Тариф, если есть
     *
     * @return bool
     */
    public function activateService(CustomerEntity $customer, ServiceEntity $service, ?ServiceTariffEntity $tariff = null): bool
    {
        // проверить была ли услуга уже подключена
        if ($this->serviceIsAssigned($customer, $service)) {
            return false;
        }

        // добавить в список активированных услуг
        $activatedService = new ServiceActivatedEntity();
        $activatedService
            ->setCustomer($customer)
            ->setService($service)
            ->setCreatedAt(new \DateTime());
        if ($tariff) {
            $activatedService->setTariff($tariff);
        }
        $customer->addService($activatedService);

        // добавить элемент истории
        $historyItem = new ServiceHistoryEntity();
        $historyItem
            ->setCreatedAt(new \DateTime())
            ->setCustomer($customer)
            ->setService($service);
        if ($tariff) {
            $historyItem->setTariff($tariff);
        }

        $this->entityManager->persist($historyItem);
        $this->entityManager->persist($activatedService);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        // диспатч события для рассылки уведомлений
        $event = new ServiceActivatedEvent($customer, $service, $tariff);
        $this->eventDispatcher->dispatch(ServiceActivatedEvent::NAME, $event);

        return true;
    }

    /**
     * Деактивация услуги для арендатора.
     *
     * Если услуга уже деактивирована - возвращает false.
     *
     * @param CustomerEntity $customer Арендатор для которого деактивировать услугу
     * @param ServiceEntity $service Деактивируемая услуга
     *
     * @return bool
     */
    public function deactivateService(CustomerEntity $customer, ServiceEntity $service): bool
    {
        if (!$this->serviceIsAssigned($customer, $service)) {
            return false;
        }

        // получение элемента истории и запись даты освобождения услуги
        $historyItems = $this->serviceHistoryRepository->findOpenedByCustomer($customer, $service);
        foreach ($historyItems as $historyItem) {
            $historyItem->setVoidedAt(new \DateTime());
            $this->entityManager->persist($historyItem);
        }

        foreach ($customer->getService() as $activatedService) {
            /** @var ServiceActivatedEntity $activatedService */
            if ($activatedService->getService()->getId() == $service->getId()) {
                $this->entityManager->remove($activatedService);
                $customer->getService()->removeElement($activatedService);
            }
        }

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        // диспатч события для рассылки уведомлений
        $event = new ServiceDeactivatedEvent($customer, $service);
        $this->eventDispatcher->dispatch(ServiceDeactivatedEvent::NAME, $event);

        return true;
    }
}
