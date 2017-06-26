<?php

namespace CustomerBundle\Event;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Событие об активированной услуге
 *
 * @package CustomerBundle\Event
 */
class ServiceActivatedEvent extends Event
{
    /**
     * Код генерируемого события
     */
    const NAME = 'service.activated';

    /**
     * @var CustomerEntity
     */
    protected $customer;

    /**
     * @var ServiceEntity
     */
    protected $service;

    /**
     * @var ServiceTariffEntity
     */
    protected $tariff;

    public function __construct(CustomerEntity $customer, ServiceEntity $service, ?ServiceTariffEntity $tariff = null)
    {
        $this->customer = $customer;
        $this->service = $service;
        $this->tariff = $tariff;
    }

    /**
     * Получить арендатора для которого активирована услуга
     *
     * @return CustomerEntity
     */
    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    /**
     * Получить активированную услугу
     *
     * @return ServiceEntity
     */
    public function getService(): ServiceEntity
    {
        return $this->service;
    }

    /**
     * Получить тариф
     *
     * @return ServiceTariffEntity|null
     */
    public function getTariff(): ?ServiceTariffEntity
    {
        return $this->tariff;
    }
}
