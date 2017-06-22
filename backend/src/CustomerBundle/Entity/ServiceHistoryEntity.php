<?php

namespace CustomerBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * История добавления и удаления услуг по договору арендатора
 *
 * @ORM\Entity()
 * @ORM\Table(name="service_customer_history")
 *
 * @package CustomerBundle\Entity
 */
class ServiceHistoryEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="id", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int Идентификтор
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var CustomerEntity Арендатор для услуги
     */
    protected $customer;

    /**
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
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     *
     * @var \DateTime Дата подключения услуги
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="voided_at")
     *
     * @var \DateTime Дата отключения услуги
     */
    protected $voidedAt;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return CustomerEntity
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return ServiceHistoryEntity
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
     * @return ServiceHistoryEntity
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
     * @return ServiceHistoryEntity
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
     * @return ServiceHistoryEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getVoidedAt(): ?\DateTime
    {
        return $this->voidedAt;
    }

    /**
     * @param \DateTime $voidedAt
     *
     * @return ServiceHistoryEntity
     */
    public function setVoidedAt(\DateTime $voidedAt): self
    {
        $this->voidedAt = $voidedAt;
        return $this;
    }
}
