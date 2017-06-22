<?php

namespace CustomerBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Модель дополнительной услуги для подключения
 *
 * @ORM\Entity(repositoryClass="CustomerBundle\Entity\Repository\ServiceRepository")
 * @ORM\Table(name="`service`")
 *
 * @package CustomerBundle\Entity
 */
class ServiceEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", name="id", length=20, nullable=false, unique=true)
     *
     * @var string Код услуги
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", name="is_active", nullable=false)
     *
     * @var boolean Активная услуга
     */
    protected $isActive;

    /**
     * @ORM\Column(type="string", name="title", length=50, nullable=false)
     *
     * @var string Заголовок услуги
     */
    protected $title;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     *
     * @var string Описание
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=50, name="customer_role", nullable=true)
     *
     * @var string При активации услуги активируется возможность подключения роли
     */
    protected $enableCustomerRole;

    /**
     * @ORM\OneToMany(targetEntity="CustomerBundle\Entity\ServiceTariffEntity", mappedBy="service", cascade={"persist", "remove"})
     *
     * @var ArrayCollection Массив тарифов
     */
    protected $tariff;

    public function __construct()
    {
        $this->tariff = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @return ServiceEntity
     */
    public function setId(string $id): self
    {
        $this->id = $id;
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
     * @return ServiceEntity
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return ServiceEntity
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ServiceEntity
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get customer role
     *
     * @return string
     */
    public function getEnableCustomerRole(): ?string
    {
        return $this->enableCustomerRole;
    }

    /**
     * Set customer role
     *
     * @param string $enableCustomerRole
     *
     * @return ServiceEntity
     */
    public function setEnableCustomerRole(?string $enableCustomerRole): self
    {
        $this->enableCustomerRole = $enableCustomerRole;
        return $this;
    }

    /**
     * Get tariff array
     *
     * @return Collection
     */
    public function getTariff(): Collection
    {
        return $this->tariff;
    }

    /**
     * Add tariff
     *
     * @param ServiceTariffEntity $tariff
     *
     * @return ServiceEntity
     */
    public function addTariff(ServiceTariffEntity $tariff): self
    {
        $this->tariff[] = $tariff;
        return $this;
    }

    /**
     * Remove tariff
     *
     * @param ServiceTariffEntity $tariff
     *
     * @return ServiceEntity
     */
    public function removeTariff(ServiceTariffEntity $tariff): self
    {
        $this->tariff->removeElement($tariff);
        return $this;
    }
}
