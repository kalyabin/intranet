<?php

namespace RentBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Модель переговорной комнаты
 *
 * @ORM\Entity()
 * @ORM\Table(name="rent_room")
 *
 * @package RentBundle\Entity
 */
class RoomEntity
{
    /**
     * Переговорка
     */
    const TYPE_MEETING = 'meeting';

    /**
     * Конференц-зал
     */
    const TYPE_CONFERENCE = 'conference';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="bigint", name="id", nullable=false)
     *
     * @var integer Идентификатор комнаты
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="`type`", length=20, nullable=false)
     *
     * @var string Тип помещения (на основе констант)
     */
    protected $type;

    /**
     * @ORM\Column(type="string", name="title", length=100, nullable=false)
     *
     * @var string Заголовок комнаты
     */
    protected $title;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     *
     * @var string Описание комнаты
     */
    protected $description;

    /**
     * @ORM\Column(type="string", name="address", nullable=false)
     *
     * @var string Адрес помещения
     */
    protected $address;

    /**
     * @ORM\Column(type="float", name="hourly_cost", nullable=false)
     *
     * @var float Стоимость в час
     */
    protected $hourlyCost;

    /**
     * @ORM\Column(type="json_array", name="schedule", nullable=false)
     *
     * График работы JSON.
     * Всего должно быть 7 элементов (на каждый день недели).
     * Каждый элемент должен содержать:
     * - weekday - порядковый день недели;
     * - schedule - массив диапазонов времени (каждый диапазон содержит from, to);
     *
     * @var array
     */
    protected $schedule;

    /**
     * @ORM\Column(type="json_array", name="schedule_break", nullable=false)
     *
     * Обязательный перерыв в расписании для каждого рабочего дня недели (например, обеденный перерыв)
     * Заносится в JSON-формате:
     * - from - время начала действия в формате HH:mm;
     * - to - время окончания действия в формате HH:mm.
     *
     * @var array
     */
    protected $scheduleBreak;

    /**
     * @ORM\Column(type="json_array", name="holidays", nullable=false)
     *
     * Праздничные дни.
     * Каждый праздничный день в формае d.m.Y.
     *
     * @var array
     */
    protected $holidays;

    /**
     * @ORM\Column(type="integer", name="request_pause", nullable=true)
     *
     * @var integer Перерыв в минутах между заявками на бронироване комнат
     */
    protected $requestPause;

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
     * Set type
     *
     * @param string $type
     *
     * @return RoomEntity
     */
    public function setType(string $type): self
    {
        $this->type = $type;

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
     * Set title
     *
     * @param string $title
     *
     * @return RoomEntity
     */
    public function setTitle(string $title): self
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
     * Set description
     *
     * @param string $description
     *
     * @return RoomEntity
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     * Set address
     *
     * @param string $address
     *
     * @return RoomEntity
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Set hourlyCost
     *
     * @param float $hourlyCost
     *
     * @return RoomEntity
     */
    public function setHourlyCost(float $hourlyCost): self
    {
        $this->hourlyCost = $hourlyCost;

        return $this;
    }

    /**
     * Get hourlyCost
     *
     * @return float
     */
    public function getHourlyCost(): ?float
    {
        return $this->hourlyCost;
    }

    /**
     * Set schedule
     *
     * @param array $schedule
     *
     * @return RoomEntity
     */
    public function setSchedule(array $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return array
     */
    public function getSchedule(): ?array
    {
        return $this->schedule;
    }

    /**
     * Set scheduleBreak
     *
     * @param array $scheduleBreak
     *
     * @return RoomEntity
     */
    public function setScheduleBreak(array $scheduleBreak): self
    {
        $this->scheduleBreak = $scheduleBreak;

        return $this;
    }

    /**
     * Get scheduleBreak
     *
     * @return array
     */
    public function getScheduleBreak(): ?array
    {
        return $this->scheduleBreak;
    }

    /**
     * Set holidays
     *
     * @param array $holidays
     *
     * @return RoomEntity
     */
    public function setHolidays(array $holidays): self
    {
        $this->holidays = $holidays;

        return $this;
    }

    /**
     * Get holidays
     *
     * @return array
     */
    public function getHolidays(): ?array
    {
        return $this->holidays;
    }

    /**
     * Set requestPause
     *
     * @param integer $requestPause
     *
     * @return RoomEntity
     */
    public function setRequestPause(int $requestPause): self
    {
        $this->requestPause = $requestPause;

        return $this;
    }

    /**
     * Get requestPause
     *
     * @return integer
     */
    public function getRequestPause(): ?int
    {
        return $this->requestPause;
    }
}
