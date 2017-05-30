<?php

namespace TicketBundle\Entity;

use DateTime;
use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\UserEntity;

/**
 * Модель тикета
 *
 * @package TicketBundle\Entity
 *
 * @ORM\Entity(repositoryClass="TicketBundle\Entity\Repository\TicketRepository")
 * @ORM\Table(name="ticket")
 */
class TicketEntity implements \JsonSerializable
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
     * Переоткрытая заявка
     */
    const STATUS_REOPENED = 'reopened';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint", nullable=false)
     *
     * @var integer Идентификатор
     */
    protected $id;

    /**
     * @ORM\Column(name="number", type="string", length=100, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=100)
     *
     * @var string Номер заявки
     */
    protected $number;

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
     * @ORM\OneToMany(targetEntity="TicketBundle\Entity\TicketMessageEntity", mappedBy="ticket", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     *
     * @var ArrayCollection Сообщения по тикету
     */
    protected $message;

    /**
     * @ORM\OneToMany(targetEntity="TicketBundle\Entity\TicketHistoryEntity", mappedBy="ticket", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     *
     * @var ArrayCollection История по тикету
     */
    protected $history;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->message = new ArrayCollection();
        $this->history = new ArrayCollection();
    }

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
     * Set number
     *
     * @param string $number
     *
     * @return TicketEntity
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber(): ?string
    {
        return $this->number;
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
    public function setLastAnswerAt(?DateTime $lastAnswerAt): self
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
    public function setVoidedAt(?DateTime $voidedAt): self
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

    /**
     * Add message
     *
     * @param \TicketBundle\Entity\TicketMessageEntity $message
     *
     * @return TicketEntity
     */
    public function addMessage(\TicketBundle\Entity\TicketMessageEntity $message): self
    {
        $this->message[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \TicketBundle\Entity\TicketMessageEntity $message
     */
    public function removeMessage(\TicketBundle\Entity\TicketMessageEntity $message)
    {
        $this->message->removeElement($message);
    }

    /**
     * Add history item
     *
     * @param TicketHistoryEntity $history
     *
     * @return TicketEntity
     */
    public function addHistory(TicketHistoryEntity $history): self
    {
        $this->history[] = $history;

        return $this;
    }

    /**
     * Remove history item
     *
     * @param TicketHistoryEntity $history
     */
    public function removeHistory(TicketHistoryEntity $history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Get history
     *
     * @return Collection
     */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    /**
     * Get message
     *
     * @return Collection
     */
    public function getMessage(): Collection
    {
        return $this->message;
    }

    /**
     * Сериализация для REST
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $dateFormat = 'Y-m-d H:i:s';

        return [
            'id' => $this->getId(),
            'number' => $this->getNumber(),
            'createdAt' => $this->getCreatedAt()->format($dateFormat),
            'createdBy' => $this->getCreatedBy(),
            'managedBy' => $this->getManagedBy(),
            'category' => $this->getCategory()->getId(),
            'currentStatus' => $this->getCurrentStatus(),
            'lastQuestionAt' => $this->getLastQuestionAt() ? $this->getLastQuestionAt()->format($dateFormat) : null,
            'lastAnswerAt' => $this->getLastAnswerAt() ? $this->getLastAnswerAt()->format($dateFormat) : null,
            'voidedAt' => $this->getVoidedAt() ? $this->getVoidedAt()->format($dateFormat) : null,
            'customer' => $this->getCustomer(),
            'title' => $this->getTitle(),
        ];
    }
}
