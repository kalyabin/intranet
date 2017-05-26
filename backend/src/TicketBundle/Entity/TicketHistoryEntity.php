<?php

namespace TicketBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\UserEntity;


/**
 * История изменений статусов по заявке
 *
 * @ORM\Entity()
 * @ORM\Table(name="ticket_history")
 *
 * @package TicketBundle\Entity
 */
class TicketHistoryEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="bigint", name="id", unique=true, nullable=false)
     *
     * @var integer Идентификатор элемента истории
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TicketBundle\Entity\TicketEntity", inversedBy="history")
     * @ORM\JoinColumn(name="ticket", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var TicketEntity Привязка к тикету
     */
    protected $ticket;

    /**
     * @ORM\Column(type="datetime", name="created_at", unique=false, nullable=false)
     *
     * @var \DateTime Дата изменения статуса
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var UserEntity Пользователь, сменивший статус заявки
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="string", length=20, nullable=false, unique=false)
     *
     * @var string Статус заявки
     */
    protected $status;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get ticket
     *
     * @return TicketEntity
     */
    public function getTicket(): ?TicketEntity
    {
        return $this->ticket;
    }

    /**
     * Set ticket
     *
     * @param TicketEntity $ticket
     *
     * @return TicketHistoryEntity
     */
    public function setTicket(TicketEntity $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set date
     *
     * @param \DateTime $createdAt
     *
     * @return TicketHistoryEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get author
     *
     * @return UserEntity
     */
    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    /**
     * Set author
     *
     * @param UserEntity $createdBy
     *
     * @return TicketHistoryEntity
     */
    public function setCreatedBy(?UserEntity $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return TicketHistoryEntity
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
