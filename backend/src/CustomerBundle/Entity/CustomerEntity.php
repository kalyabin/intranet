<?php

namespace CustomerBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Assert\Type(type="boolean")
     *
     * @var boolean Доступ к IT-аутсорсингу
     */
    private $allowItDepartment = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Assert\Type(type="boolean")
     *
     * @var boolean Доступ к службе SMART-бухгалтера
     */
    private $allowBookerDepartment = false;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserEntity", mappedBy="customer", cascade={"persist", "remove"})
     *
     * @var ArrayCollection Привязка к пользователям
     */
    private $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
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
     * @return boolean
     */
    public function getAllowItDepartment(): bool
    {
        return $this->allowItDepartment === true;
    }

    /**
     * @param boolean $allowItDepartment
     *
     * @return CustomerEntity
     */
    public function setAllowItDepartment(?bool $allowItDepartment): self
    {
        $this->allowItDepartment = $allowItDepartment === true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowBookerDepartment(): bool
    {
        return $this->allowBookerDepartment === true;
    }

    /**
     * @param boolean $allowBookerDepartment
     *
     * @return CustomerEntity
     */
    public function setAllowBookerDepartment(?bool $allowBookerDepartment): self
    {
        $this->allowBookerDepartment = $allowBookerDepartment === true;
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
            'allowItDepartment' => $this->getAllowItDepartment(),
            'allowBookerDepartment' => $this->getAllowBookerDepartment(),
        ];
    }
}
