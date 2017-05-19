<?php

namespace TicketBundle\Entity;

use DateTime;
use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\UserEntity;

/**
 * Модель тикета
 *
 * @ORM\Entity()
 * @ORM\Table(name="ticket")
 *
 * @package TicketBundle\Entity
 */
class TicketEntity
{
    /**
     * Статус заявки - новая
     */
    const STATUS_NEW = 'new';

    /**
     * Статус заявки - поступила в работу
     */
    const STATUS_IN_PROCESS = 'in_process';

    /**
     * Статус заявки - поступила в работу
     */
    const STATUS_ANSWERED = 'answered';

    /**
     * Статус заявки - ожидает ответа
     */
    const STATUS_WAIT = 'wait';

    /**
     * Статус заявки - закрыта
     */
    const STATUS_CLOSED = 'closed';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint", nullable=false)
     *
     * @var integer Идентификатор
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @var DateTime Дата создания тикета
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="last_question_at", type="datetime", nullable=false)
     *
     * @var DateTime Дата последнего вопроса по тикету
     */
    protected $lastQuestionAt;

    /**
     * @ORM\Column(name="last_answer_at", type="datetime", nullable=true)
     *
     * @var DateTime Дата последнего ответа по тикету
     */
    protected $lastAnswerAt;

    /**
     * @ORM\Column(name="voided_at", type="datetime", nullable=true)
     *
     * @var DateTime Дата автоматического закрытия заявки
     */
    protected $voidedAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var UserEntity Создатель тикета
     */
    protected $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="managed_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var UserEntity Ответственный менеджер
     */
    protected $managedBy;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var CustomerEntity Арендатор, создавший тикет
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", name="current_status", length=20)
     *
     * @Assert\Choice(callback="getStatuses")
     *
     * @var string Текущий статус заявки
     */
    protected $currentStatus;

    /**
     * @ORM\ManyToOne(targetEntity="TicketBundle\Entity\TicketCategoryEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var TicketCategoryEntity Привязка к категории заявки
     */
    protected $category;

    /**
     * @ORM\Column(type="text", name="title", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="200")
     *
     * @var string
     */
    protected $title;

    /**
     * Получить текстовое описание статусов
     *
     * @return array
     */
    public function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_IN_PROCESS => 'В работе',
            self::STATUS_ANSWERED => 'Поступил ответ',
            self::STATUS_WAIT => 'Ожидает ответа',
            self::STATUS_CLOSED => 'Закрыта',
        ];
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TicketEntity
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set lastQuestionAt
     *
     * @param \DateTime $lastQuestionAt
     *
     * @return TicketEntity
     */
    public function setLastQuestionAt(DateTime $lastQuestionAt): self
    {
        $this->lastQuestionAt = $lastQuestionAt;

        return $this;
    }

    /**
     * Get lastQuestionAt
     *
     * @return \DateTime
     */
    public function getLastQuestionAt(): ?DateTime
    {
        return $this->lastQuestionAt;
    }

    /**
     * Set lastAnswerAt
     *
     * @param \DateTime $lastAnswerAt
     *
     * @return TicketEntity
     */
    public function setLastAnswerAt(DateTime $lastAnswerAt): self
    {
        $this->lastAnswerAt = $lastAnswerAt;

        return $this;
    }

    /**
     * Get lastAnswerAt
     *
     * @return \DateTime
     */
    public function getLastAnswerAt(): ?DateTime
    {
        return $this->lastAnswerAt;
    }

    /**
     * Set voidedAt
     *
     * @param \DateTime $voidedAt
     *
     * @return TicketEntity
     */
    public function setVoidedAt(DateTime $voidedAt): self
    {
        $this->voidedAt = $voidedAt;

        return $this;
    }

    /**
     * Get voidedAt
     *
     * @return \DateTime
     */
    public function getVoidedAt(): ?DateTime
    {
        return $this->voidedAt;
    }

    /**
     * Set currentStatus
     *
     * @param string $currentStatus
     *
     * @return TicketEntity
     */
    public function setCurrentStatus(?string $currentStatus): self
    {
        $this->currentStatus = $currentStatus;

        return $this;
    }

    /**
     * Get currentStatus
     *
     * @return string
     */
    public function getCurrentStatus(): ?string
    {
        return $this->currentStatus;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return TicketEntity
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

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
     * Set createdBy
     *
     * @param \UserBundle\Entity\UserEntity $createdBy
     *
     * @return TicketEntity
     */
    public function setCreatedBy(\UserBundle\Entity\UserEntity $createdBy = null): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \UserBundle\Entity\UserEntity
     */
    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    /**
     * Set managedBy
     *
     * @param \UserBundle\Entity\UserEntity $managedBy
     *
     * @return TicketEntity
     */
    public function setManagedBy(\UserBundle\Entity\UserEntity $managedBy = null): self
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    /**
     * Get managedBy
     *
     * @return \UserBundle\Entity\UserEntity
     */
    public function getManagedBy(): ?UserEntity
    {
        return $this->managedBy;
    }

    /**
     * Set customer
     *
     * @param \CustomerBundle\Entity\CustomerEntity $customer
     *
     * @return TicketEntity
     */
    public function setCustomer(\CustomerBundle\Entity\CustomerEntity $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \CustomerBundle\Entity\CustomerEntity
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * Set category
     *
     * @param \TicketBundle\Entity\TicketCategoryEntity $category
     *
     * @return TicketEntity
     */
    public function setCategory(\TicketBundle\Entity\TicketCategoryEntity $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \TicketBundle\Entity\TicketCategoryEntity
     */
    public function getCategory(): ?TicketCategoryEntity
    {
        return $this->category;
    }
}
