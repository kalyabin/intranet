<?php

namespace CustomerBundle\Event;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceEntity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Событие о деактивации услуги
 *
 * @package CustomerBundle\Event
 */
class ServiceDeactivatedEvent extends Event
{
    /**
     * Код события
     */
    const NAME = 'service.deactivated';

    /**
     * @var CustomerEntity
     */
    protected $customer;

    /**
     * @var ServiceEntity
     */
    protected $service;

    public function __construct(CustomerEntity $customer, ServiceEntity $service)
    {
        $this->customer = $customer;
        $this->service = $service;
    }

    /**
     * Получить арендатора
     *
     * @return CustomerEntity
     */
    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    /**
     * Получить услугу
     *
     * @return ServiceEntity
     */
    public function getService(): ServiceEntity
    {
        return $this->service;
    }
}
