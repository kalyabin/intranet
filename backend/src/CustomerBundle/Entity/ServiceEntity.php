<?php

namespace CustomerBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\UserEntity;
use UserBundle\Validator\Constraints\UserRole;

/**
 * Модель дополнительной услуги для подключения
 *
 * @ORM\Entity(repositoryClass="CustomerBundle\Entity\Repository\ServiceRepository")
 * @ORM\Table(name="`service`")
 * @UniqueEntity(fields={"id"}, message="Услуга с таким кодом уже существует")
 *
 * @package CustomerBundle\Entity
 */
class ServiceEntity implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", name="id", length=50, nullable=false, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9-_]+$/i",
     *     message="Код должен содержать только буквы латинского алфавита, цифры, знак тире или знак подчеркивания"
     * )
     *
     * @var string Код услуги
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", name="is_active", nullable=false)
     *
     * @Assert\Type("bool")
     *
     * @var boolean Активная услуга
     */
    protected $isActive;

    /**
     * @ORM\Column(type="string", name="title", length=50, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     *
     * @var string Заголовок услуги
     */
    protected $title;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     *
     * @Assert\Length(max=1000)
     *
     * @var string Описание
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=50, name="customer_role", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     * @UserRole(message="Роль не найдена для типа пользователя Арендатор", userTypeCallback="getCustomerUserType")
     *
     * @var string При активации услуги активируется возможность подключения роли
     */
    protected $customerRole;

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
    public function getCustomerRole(): ?string
    {
        return $this->customerRole;
    }

    /**
     * Set customer role
     *
     * @param string $customerRole
     *
     * @return ServiceEntity
     */
    public function setCustomerRole(?string $customerRole): self
    {
        $this->customerRole = $customerRole;
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
        $tariff->setService($this);
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

    /**
     * Получение кода типа пользователя "Арендатор" для валидации ролей арендатора
     *
     * @return string
     */
    public function getCustomerUserType(): string
    {
        return UserEntity::TYPE_CUSTOMER;
    }

    /**
     * Сериализация в JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'isActive' => $this->getIsActive(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'customerRole' => $this->getCustomerRole(),
            'tariff' => $this->getTariff()->getValues(),
        ];
    }
}
