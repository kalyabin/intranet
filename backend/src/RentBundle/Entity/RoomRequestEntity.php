<?php

namespace RentBundle\Entity;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Заявка на бронирование переговорной комнаты
 *
 * @ORM\Entity(repositoryClass="RentBundle\Entity\Repository\RoomRequestRepository")
 * @ORM\Table(name="room_request")
 *
 * @package RentBundle\Entity
 */
class RoomRequestEntity implements \JsonSerializable
{
    /**
     * Ожидание подтверждения
     */
    const STATUS_PENDING = 'pending';

    /**
     * Заявка подтверждена
     */
    const STATUS_APPROVED = 'approved';

    /**
     * Отказ заявки
     */
    const STATUS_DECLINED = 'declined';

    /**
     * Отмена со стороны арендатора
     */
    const STATUS_CANCELED = 'cancelled';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="bigint", name="id", nullable=false)
     *
     * @var integer Идентификатор заявки
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=false)
     *
     * @var \DateTime Дата и время создания заявки
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="string", name="status", length=20, nullable=false)
     *
     * @Assert\Type(type="string")
     * @Assert\Choice(callback="getStatuses", strict=true)
     *
     * @var string Статус заявки
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="RentBundle\Entity\RoomEntity")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank()
     *
     * @var RoomEntity Привязка к помещению для бронирования
     */
    protected $room;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerBundle\Entity\CustomerEntity")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var CustomerEntity Привязка к арендатору, забронировавшему помещение
     */
    protected $customer;

    /**
     * @ORM\Column(type="datetime", name="`from`", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var \DateTime Дата начала действия аренды
     */
    protected $from;

    /**
     * @ORM\Column(type="datetime", name="`to`", nullable=false)
     *
     * @Assert\NotBlank()
     *
     * @var \DateTime Дата окончания действия аренды
     */
    protected $to;

    /**
     * @ORM\Column(type="text", name="manager_comment", nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(max="1000")
     *
     * @var string Комментарий менеджера (например по статусу заявки)
     */
    protected $managerComment;

    /**
     * @ORM\Column(type="text", name="customer_comment", nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(max="1000")
     *
     * @var string Комментарий арендатора
     */
    protected $customerComment;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return RoomRequestEntity
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return RoomRequestEntity
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

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
     * Получить текст статуса
     *
     * @return null|string
     */
    public function getStatusName(): ?string
    {
        switch ($this->status) {
            case self::STATUS_DECLINED:
                return 'Отказана';
                break;

            case self::STATUS_CANCELED:
                return 'Отменена';
                break;

            case self::STATUS_PENDING:
                return 'Ожидает подтверждения';
                break;

            case self::STATUS_APPROVED:
                return 'Подтверждена';
                break;
        }

        return null;
    }

    /**
     * Set from
     *
     * @param \DateTime $from
     *
     * @return RoomRequestEntity
     */
    public function setFrom(?\DateTime $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \DateTime
     */
    public function getFrom(): ?\DateTime
    {
        return $this->from;
    }

    /**
     * Set to
     *
     * @param \DateTime $to
     *
     * @return RoomRequestEntity
     */
    public function setTo(?\DateTime $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return \DateTime
     */
    public function getTo(): ?\DateTime
    {
        return $this->to;
    }

    /**
     * Set room
     *
     * @param \RentBundle\Entity\RoomEntity $room
     *
     * @return RoomRequestEntity
     */
    public function setRoom(\RentBundle\Entity\RoomEntity $room): self
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \RentBundle\Entity\RoomEntity
     */
    public function getRoom(): ?RoomEntity
    {
        return $this->room;
    }

    /**
     * Set customer
     *
     * @param \CustomerBundle\Entity\CustomerEntity $customer
     *
     * @return RoomRequestEntity
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
     * Set managerComment
     *
     * @param string $managerComment
     *
     * @return RoomRequestEntity
     */
    public function setManagerComment(string $managerComment): self
    {
        $this->managerComment = $managerComment;

        return $this;
    }

    /**
     * Get managerComment
     *
     * @return string
     */
    public function getManagerComment(): ?string
    {
        return $this->managerComment;
    }

    /**
     * Set customerComment
     *
     * @param string $customerComment
     *
     * @return RoomRequestEntity
     */
    public function setCustomerComment(string $customerComment): self
    {
        $this->customerComment = $customerComment;

        return $this;
    }

    /**
     * Get customerComment
     *
     * @return string
     */
    public function getCustomerComment(): ?string
    {
        return $this->customerComment;
    }

    /**
     * Валидация дат
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     * @param mixed $payload
     */
    public function validateDates(ExecutionContextInterface $context, $payload)
    {
        if (!$this->from instanceof \DateTime) {
            $context->buildViolation('Неверный формат даты в поле "с"')
                ->atPath('from')
                ->addViolation();
            return;
        }

        if (!$this->to instanceof \DateTime) {
            $context->buildViolation('Неверный формат даты в поле "по"')
                ->atPath('to')
                ->addViolation();
            return;
        }

        if ($this->to->getTimestamp() <= $this->from->getTimestamp()) {
            $context->buildViolation('Поле "с" не может быть больше или равно полю "по"')
                ->atPath('from')
                ->addViolation();
            return;
        }
    }

    /**
     * Получить доступные статусы для валидации
     *
     * @return string[]
     */
    public static function getStatuses(): array
    {
        return [self::STATUS_APPROVED, self::STATUS_CANCELED, self::STATUS_DECLINED, self::STATUS_PENDING];
    }

    /**
     * Возвращает true, если заявка была или будет исполнена
     *
     * @return bool
     */
    public function isOpened(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PENDING]);
    }

    /**
     * Возвращает true, если заявка была отменена или отказана
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELED, self::STATUS_DECLINED]);
    }

    /**
     * Сериализация в JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $dateFormat = 'Y-m-d H:i';

        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format($dateFormat) : null,
            'status' => $this->getStatus(),
            'room' => $this->getRoom(),
            'customer' => $this->getCustomer(),
            'from' => $this->getFrom() ? $this->getFrom()->format($dateFormat) : null,
            'to' => $this->getTo() ? $this->getTo()->format($dateFormat) : null,
            'managerComment' => $this->getManagerComment(),
            'customerComment' => $this->getCustomerComment(),
        ];
    }
}
