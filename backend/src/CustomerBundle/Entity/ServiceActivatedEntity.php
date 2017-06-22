<?php

namespace CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Модель активированной в данный момент услуги арендатором
 *
 * @ORM\Entity()
 * @ORM\Table(name="service_activated")
 *
 * @package CustomerBundle\Tests\Entity
 */
class ServiceActivatedEntity
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity", inversedBy="service")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var CustomerEntity Арендатор, для которого активирована услуга
     */
    protected $customer;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\ServiceEntity")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var ServiceEntity Подключенная услуга
     */
    protected $service;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\ServiceTariffEntity")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var ServiceTariffEntity Подключенный тариф
     */
    protected $tariff;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=false)
     *
     * @var \DateTime Дата подключения услуги
     */
    protected $createdAt;

    /**
     * @return \CustomerBundle\Entity\CustomerEntity
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param \CustomerBundle\Entity\CustomerEntity $customer
     *
     * @return ServiceActivatedEntity
     */
    public function setCustomer(CustomerEntity $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return ServiceEntity
     */
    public function getService(): ?ServiceEntity
    {
        return $this->service;
    }

    /**
     * @param ServiceEntity $service
     *
     * @return ServiceActivatedEntity
     */
    public function setService(ServiceEntity $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return ServiceTariffEntity
     */
    public function getTariff(): ?ServiceTariffEntity
    {
        return $this->tariff;
    }

    /**
     * @param ServiceTariffEntity $tariff
     *
     * @return ServiceActivatedEntity
     */
    public function setTariff(ServiceTariffEntity $tariff): self
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return ServiceActivatedEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
