<?php

namespace TicketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Категория (очередь) внутри тикетной системы.
 *
 * Обладает:
 * - ролью менеджера, которая разрешает просмотр и работу менеджера внутри категории
 * - ролью арендатора, которая разрешает просмотр и отправку заявок арендатором.
 *
 * @ORM\Entity(repositoryClass="TicketBundle\Entity\Repository\TicketCategoryRepository")
 * @ORM\Table(name="ticket_category")
 *
 * @package TicketBundle\Entity
 */
class TicketCategoryEntity implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=50, name="id", nullable=false, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     *
     * @var string идентификатор (код) категории
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, name="name", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Название очереди
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100, name="manager_role", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Роль сотрудника для доступа к очереди
     */
    protected $managerRole;

    /**
     * @ORM\Column(type="string", length=100, name="customer_role", nullable=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Роль арендатора для доступа к очереди. По умолчанию - null, доступ имеют все арендаторы.
     */
    protected $customerRole;

    /**
     * Получить идентификатор
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Установить идентификатор
     *
     * @param string $id
     *
     * @return TicketCategoryEntity
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Получить название
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Установить название
     *
     * @param string $name
     *
     * @return TicketCategoryEntity
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Получить роль сотрудника
     *
     * @return string
     */
    public function getManagerRole(): string
    {
        return $this->managerRole;
    }

    /**
     * Установить роль сотрудника
     *
     * @param string $managerRole
     *
     * @return TicketCategoryEntity
     */
    public function setManagerRole(string $managerRole): self
    {
        $this->managerRole = $managerRole;
        return $this;
    }

    /**
     * Получить роль арендатора
     *
     * @return string
     */
    public function getCustomerRole(): ?string
    {
        return $this->customerRole;
    }

    /**
     * Установить роль арендатора
     *
     * @param string $customerRole
     *
     * @return TicketCategoryEntity
     */
    public function setCustomerRole(string $customerRole): self
    {
        $this->customerRole = $customerRole;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'managerRole' => $this->getManagerRole(),
            'customerRole' => $this->getCustomerRole()
        ];
    }
}
