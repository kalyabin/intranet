<?php

namespace CustomerBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Модель для описания тарифов услуги
 *
 * @ORM\Entity()
 * @ORM\Table(name="service_tariff")
 *
 * @package CustomerBundle\Entity
 */
class ServiceTariffEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="id", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var string Идентификатор
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\ServiceEntity", inversedBy="tariff")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var ServiceEntity Привязка к доп. услуге
     */
    protected $service;

    /**
     * @ORM\Column(type="boolean", name="is_active", nullable=false)
     *
     * @var boolean Активный тариф
     */
    protected $isActive;

    /**
     * @ORM\Column(type="string", length=50, name="title")
     *
     * @var string Заголовок тарифа
     */
    protected $title;

    /**
     * @ORM\Column(type="float", name="monthly_cost", nullable=true)
     *
     * @var float Ежемесячный платёж
     */
    protected $monthlyCost;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
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
     * @return ServiceTariffEntity
     */
    public function setService(ServiceEntity $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return ServiceTariffEntity
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ServiceTariffEntity
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return float
     */
    public function getMonthlyCost(): ?float
    {
        return $this->monthlyCost;
    }

    /**
     * @param float $monthlyCost
     *
     * @return ServiceTariffEntity
     */
    public function setMonthlyCost(?float $monthlyCost): self
    {
        $this->monthlyCost = $monthlyCost;
        return $this;
    }
}
