<?php

namespace CustomerBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\UserEntity;

/**
 * Модель контрагента (арендатора)
 *
 * @ORM\Entity(repositoryClass="CustomerBundle\Entity\Repository\CustomerRepository")
 * @ORM\Table(name="customer")
 *
 * @package CustomerBundle\Entity
 */
class CustomerEntity implements \JsonSerializable
{
    /**
     * @ORM\Column(type="bigint", name="id")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer Идентификатор контрагента
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max=100)
     *
     * @var string Название контрагента
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(max=100)
     *
     * @var string Номер текущего договора
     */
    private $currentAgreement;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserEntity", mappedBy="customer", cascade={"persist", "remove"})
     *
     * @var ArrayCollection Привязка к пользователям
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="CustomerBundle\Entity\ServiceActivatedEntity", mappedBy="customer", cascade={"persist", "remove"})
     *
     * @var ArrayCollection Активированные услуги
     */
    private $service;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->service = new ArrayCollection();
    }

    /**
     * Добавить пользователя к контрагенту
     *
     * @param UserEntity $user
     *
     * @return CustomerEntity
     */
    public function addUser(UserEntity $user): self
    {
        $user->setCustomer($this);
        $this->user[] = $user;
        return $this;
    }

    /**
     * Удалить пользователя от контрагента
     *
     * @param UserEntity $user
     */
    public function removeUser(UserEntity $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return CustomerEntity
     */
    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentAgreement(): ?string
    {
        return $this->currentAgreement;
    }

    /**
     * @param string $currentAgreement
     *
     * @return CustomerEntity
     */
    public function setCurrentAgreement(?string $currentAgreement): self
    {
        $this->currentAgreement = $currentAgreement;
        return $this;
    }

    /**
     * Получить подключенные услуги
     *
     * @return Collection
     */
    public function getService(): Collection
    {
        return $this->service;
    }

    /**
     * Добавить услугу
     *
     * @param ServiceActivatedEntity $entity
     *
     * @return CustomerEntity
     */
    public function addService(ServiceActivatedEntity $entity): self
    {
        $this->service[] = $entity;
        return $this;
    }

    /**
     * Удалить услугу
     *
     * @param ServiceActivatedEntity $entity
     *
     * @return CustomerEntity
     */
    public function removeService(ServiceActivatedEntity $entity): self
    {
        $this->service->removeElement($entity);
        return $this;
    }

    /**
     * Формирование объекта для рестов
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'currentAgreement' => $this->getCurrentAgreement(),
        ];
    }
}
