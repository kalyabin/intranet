<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use UserBundle\Entity\UserEntity;


/**
 * Модель системного уведомления для пользователя.
 *
 * В зависимости от типа уведомления привязываются дополнительные поля.
 * Они указываются как null, необязательные.
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserNotificationRepository")
 * @ORM\Table(name="user_notification")
 *
 * @package AppBundle\Entity
 */
class UserNotificationEntity implements \JsonSerializable
{
    /**
     * Новая заявка в тикетной системе
     */
    const TYPE_TICKET_NEW = 'ticket_new';

    /**
     * Новое сообщение в заявке тикетной системы
     */
    const TYPE_TICKET_NEW_MESSAGE = 'ticket_message';

    /**
     * Установлен менеджер в заявке в тикетной системе
     */
    const TYPE_TICKET_MANAGER_SET = 'ticket_manager_set';

    /**
     * Закрыта заявка в тикетной системе
     */
    const TYPE_TICKET_CLOSED = 'ticket_closed';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     *
     * @var int Идентификатор
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @var \DateTime Дата создания
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="`type`", type="string", length=20, nullable=false)
     *
     * @var string Тип уведомления
     */
    protected $type;

    /**
     * @ORM\Column(name="is_read", type="boolean", nullable=false)
     *
     * @var boolean Уведомление было прочтено
     */
    protected $isRead;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="receiver", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var UserEntity Пользователь, для которого создано уведомление
     */
    protected $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="author", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     *
     * @var UserEntity Пользователь, который синицировал уведомление
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="TicketBundle\Entity\TicketEntity")
     * @ORM\JoinColumn(name="ticket", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var TicketEntity Привязка к заявке в тикетной системе
     */
    protected $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="TicketBundle\Entity\TicketMessageEntity")
     * @ORM\JoinColumn(name="ticket_message", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var TicketMessageEntity Привязка к сообщению в тикетной системе
     */
    protected $ticketMessage;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="ticket_manager", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var UserEntity Привязка к установленному менеджеру в тикетной системе
     */
    protected $ticketManager;

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
     * Get created date
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set created date
     *
     * @param \DateTime $createdAt
     *
     * @return UserNotificationEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return UserNotificationEntity
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get is read flag
     *
     * @return bool
     */
    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    /**
     * Set is read flag
     *
     * @param bool $isRead
     *
     * @return UserNotificationEntity
     */
    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    /**
     * Get user for notification
     *
     * @return UserEntity
     */
    public function getReceiver(): ?UserEntity
    {
        return $this->receiver;
    }

    /**
     * Set user for notification
     *
     * @param UserEntity $receiver
     *
     * @return UserNotificationEntity
     */
    public function setReceiver(UserEntity $receiver): self
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * Get author if need
     *
     * @return UserEntity
     */
    public function getAuthor(): ?UserEntity
    {
        return $this->author;
    }

    /**
     * Set author if need
     *
     * @param UserEntity $author
     *
     * @return UserNotificationEntity
     */
    public function setAuthor(?UserEntity $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get ticket if need
     *
     * @return TicketEntity
     */
    public function getTicket(): ?TicketEntity
    {
        return $this->ticket;
    }

    /**
     * Set ticket if need
     *
     * @param TicketEntity $ticket
     *
     * @return UserNotificationEntity
     */
    public function setTicket(?TicketEntity $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * Get ticket message if need
     *
     * @return TicketMessageEntity
     */
    public function getTicketMessage(): ?TicketMessageEntity
    {
        return $this->ticketMessage;
    }

    /**
     * Set ticket message if need
     *
     * @param TicketMessageEntity $ticketMessage
     *
     * @return UserNotificationEntity
     */
    public function setTicketMessage(?TicketMessageEntity $ticketMessage): self
    {
        $this->ticketMessage = $ticketMessage;
        return $this;
    }

    /**
     * Get ticket manager if need
     *
     * @return UserEntity
     */
    public function getTicketManager(): ?UserEntity
    {
        return $this->ticketManager;
    }

    /**
     * Set ticket manager if need
     *
     * @param UserEntity $ticketManager
     *
     * @return UserNotificationEntity
     */
    public function setTicketManager(?UserEntity $ticketManager): self
    {
        $this->ticketManager = $ticketManager;
        return $this;
    }

    /**
     * Сериализация данных для JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'type' => $this->type,
            'isRead' => $this->isRead,
            'author' => $this->author,
            'ticket' => $this->ticket,
            'ticketMessage' => $this->ticketMessage,
            'ticketManager' => $this->ticketManager,
        ];
    }
}
