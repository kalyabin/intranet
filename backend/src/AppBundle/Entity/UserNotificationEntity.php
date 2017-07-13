<?php

namespace AppBundle\Entity;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\ORM\Mapping as ORM;
use RentBundle\Entity\RoomEntity;
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
     * Тип уведомления - входящий звонок
     */
    const TYPE_INCOMING_CALL = 'incoming_call';

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
     * Активирована услуга
     */
    const TYPE_SERVICE_ACTIVATED = 'service_activated';

    /**
     * Услуга деактивирована
     */
    const TYPE_SERVICE_DEACTIVATED = 'service_deactivated';

    /**
     * Создание заявки на бронирование комнаты
     */
    const TYPE_ROOM_REQUEST_CREATED = 'room_request_created';

    /**
     * Отменена заявка на бронирование комнаты со стороны пользователя
     */
    const TYPE_ROOM_REQUEST_CANCELLED = 'room_request_cancelled';

    /**
     * Изменена заявка на бронирование комнаты со стороны менеджера
     */
    const TYPE_ROOM_REQUEST_UPDATED = 'room_request_updated';

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
     * @ORM\Column(name="`type`", type="string", length=50, nullable=false)
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
     * @ORM\Column(name="caller_id", type="string", length=20, nullable=true)
     *
     * @var string Номер входящего телефона для типа уведомления "Входящий звонок"
     */
    protected $callerId;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     *
     * @var string Сопроводительный комментарий для любого типа уведомления
     */
    protected $comment;

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
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\ServiceEntity")
     * @ORM\JoinColumn(name="`service`", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var ServiceEntity Привязка к активированной или деактивированной услуге
     */
    protected $service;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\ServiceTariffEntity")
     * @ORM\JoinColumn(name="service_tariff", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var ServiceTariffEntity Привязка к тарифу активированной услуги
     */
    protected $tariff;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity")
     * @ORM\JoinColumn(name="customer", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var CustomerEntity Привязка к контрагенту
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="RentBundle\Entity\RoomEntity")
     * @ORM\JoinColumn(name="room", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var RoomEntity Комната для бронирования
     */
    protected $room;

    /**
     * @ORM\Column(name="`from`", type="datetime", nullable=true)
     *
     * @var \DateTime Время начала (например, время начала аренды для заявки аренды помещений)
     */
    protected $from;

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
     * Get caller id
     *
     * @return string
     */
    public function getCallerId(): ?string
    {
        return $this->callerId;
    }

    /**
     * Set caller id
     * @param string $callerId
     *
     * @return UserNotificationEntity
     */
    public function setCallerId(?string $callerId): self
    {
        $this->callerId = $callerId;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return UserNotificationEntity
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
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
     * @return ServiceEntity
     */
    public function getService(): ?ServiceEntity
    {
        return $this->service;
    }

    /**
     * @param ServiceEntity $service
     *
     * @return UserNotificationEntity
     */
    public function setService(ServiceEntity $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return ServiceTariffEntity
     */
    public function getTariff(): ?ServiceTariffEntity
    {
        return $this->tariff;
    }

    /**
     * @param ServiceTariffEntity $tariff
     *
     * @return UserNotificationEntity
     */
    public function setTariff(ServiceTariffEntity $tariff): self
    {
        $this->tariff = $tariff;
        return $this;
    }

    /**
     * @return CustomerEntity
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return UserNotificationEntity
     */
    public function setCustomer(CustomerEntity $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return RoomEntity
     */
    public function getRoom(): ?RoomEntity
    {
        return $this->room;
    }

    /**
     * @param RoomEntity $room
     *
     * @return UserNotificationEntity
     */
    public function setRoom(RoomEntity $room): self
    {
        $this->room = $room;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFrom(): ?\DateTime
    {
        return $this->from;
    }

    /**
     * @param \DateTime $from
     *
     * @return UserNotificationEntity
     */
    public function setFrom(\DateTime $from): self
    {
        $this->from = $from;
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
            'from' => $this->from ? $this->from->format('Y-m-d H:i') : null,
            'type' => $this->type,
            'isRead' => $this->isRead,
            'author' => $this->author,
            'ticket' => $this->ticket,
            'ticketMessage' => $this->ticketMessage,
            'ticketManager' => $this->ticketManager,
            'service' => $this->service,
            'tariff' => $this->tariff,
            'customer' => $this->customer,
            'room' => $this->room,
            'callerId' => $this->callerId,
            'comment' => $this->comment,
        ];
    }
}
