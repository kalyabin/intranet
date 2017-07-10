<?php

namespace RentBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Модель переговорной комнаты
 *
 * @ORM\Entity(repositoryClass="RentBundle\Entity\Repository\RoomRepository")
 * @ORM\Table(name="rent_room")
 *
 * @package RentBundle\Entity
 */
class RoomEntity implements \JsonSerializable
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
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Choice(callback="getRoomTypes", strict=true)
     *
     * @var string Тип помещения (на основе констант)
     */
    protected $type;

    /**
     * @ORM\Column(type="string", name="title", length=100, nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max="100")
     *
     * @var string Заголовок комнаты
     */
    protected $title;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     *
     * @Assert\Type(type="string")
     * @Assert\Length(max="1000")
     *
     * @var string Описание комнаты
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255, name="address", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max="255")
     *
     * @var string Адрес помещения
     */
    protected $address;

    /**
     * @ORM\Column(type="float", name="hourly_cost", nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     * @Assert\Range(min="0")
     *
     * @var float Стоимость в час
     */
    protected $hourlyCost;

    /**
     * @ORM\Column(type="json_array", name="schedule", nullable=true)
     *
     * @Assert\Type(type="array")
     *
     * График работы JSON.
     * Всего должно быть 7 элементов (на каждый день недели).
     * Каждый элемент должен содержать массив элементов с:
     * - from, to (формат HH:mm)
     *
     * @var array
     */
    protected $schedule;

    /**
     * @ORM\Column(type="json_array", name="schedule_break", nullable=true)
     *
     * @Assert\Type(type="array")
     *
     * Обязательный перерыв в расписании для каждого рабочего дня недели (например, обеденный перерыв)
     * Заносится в JSON-формате:
     * - from - время начала перерыва в формате HH:mm;
     * - to - время окончания перерыва в формате HH:mm.
     *
     * @var array
     */
    protected $scheduleBreak;

    /**
     * @ORM\Column(type="json_array", name="holidays", nullable=true)
     *
     * @Assert\Type(type="array")
     *
     * Праздничные дни.
     * Каждый праздничный день в формае Y-m-d.
     *
     * @var array
     */
    protected $holidays;

    /**
     * @ORM\Column(type="json_array", name="work_weekends", nullable=true)
     *
     * @Assert\Type(type="array")
     *
     * Рабочие выходные дни
     * Каждый день указывается в формате Y-m-d.
     *
     * @var array
     */
    protected $workWeekends;

    /**
     * @ORM\Column(type="integer", name="request_pause", nullable=true)
     *
     * @Assert\Type(type="integer")
     * @Assert\Range(min="0")
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
    public function setType(?string $type): self
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
     * Set description
     *
     * @param string $description
     *
     * @return RoomEntity
     */
    public function setDescription(?string $description): self
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
    public function setAddress(?string $address): self
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
    public function setHourlyCost(?float $hourlyCost): self
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
    public function setSchedule(?array $schedule): self
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
    public function setScheduleBreak(?array $scheduleBreak): self
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
    public function setHolidays(?array $holidays): self
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
     * Get working weekends
     *
     * @return array
     */
    public function getWorkWeekends(): ?array
    {
        return $this->workWeekends;
    }

    /**
     * Set working weekends
     *
     * @param array $workWeekends
     *
     * @return RoomEntity
     */
    public function setWorkWeekends(?array $workWeekends): self
    {
        $this->workWeekends = $workWeekends;
        return $this;
    }

    /**
     * Set requestPause
     *
     * @param integer $requestPause
     *
     * @return RoomEntity
     */
    public function setRequestPause(?int $requestPause): self
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

    /**
     * Типы помещений
     *
     * @return string[]
     */
    public static function getRoomTypes(): array
    {
        return [
            self::TYPE_MEETING, self::TYPE_CONFERENCE,
        ];
    }

    /**
     * Валидация еженедельного расписания
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     * @param mixed $payload
     */
    public function validateSchedule(ExecutionContextInterface $context, $payload)
    {
        $schedule = $this->getSchedule();

        $buildMessage = function(string $message) use ($context) {
            $context->buildViolation($message)
                ->atPath('schedule')
                ->addViolation();
        };

        if (empty($schedule)) {
            return;
        }

        if (!is_array($schedule) || count($schedule) > 7) {
            $buildMessage('Неверный формат расписания');
            return;
        }

        foreach ($schedule as $k => $day) {
            if (!is_array($day)) {
                $buildMessage('Неверный формат элемента расписания: ' . $k);
                return;
            }
            foreach ($day as $i => $item) {
                $message = 'Неверный формат расписания для дня недели ' . $i;
                if (isset($item['avail']) && !$item['avail']) {
                    continue;
                }
                if (!is_array($item) || empty($item['from']) || empty($item['to'])) {
                    $buildMessage($message);
                    return;
                }
                $dateFrom = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $item['from']);
                $dateTo = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $item['to']);
                if (
                    !($dateFrom instanceof \DateTime) || !($dateTo instanceof \DateTime) ||
                    $dateFrom->getTimestamp() >= $dateTo->getTimestamp()
                ) {
                    $buildMessage($message);
                    return;
                }
            }
        }
    }

    /**
     * Валидация перерывов в ежедневном расписании
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     * @param mixed $payload
     */
    public function validateScheduleBreak(ExecutionContextInterface $context, $payload)
    {
        $scheduleBreak = $this->getScheduleBreak();

        if (empty($scheduleBreak)) {
            return;
        }

        $buildMessage = function(string $message) use ($context) {
            $context->buildViolation($message)
                ->atPath('scheduleBreak')
                ->addViolation();
        };

        if (!is_array($scheduleBreak)) {
            $buildMessage('Неверный формат перерыва в расписании');
            return;
        }

        foreach ($scheduleBreak as $k => $item) {
            if (!is_array($item)) {
                $buildMessage('Неверный формат перерыва в расписании (элемент: ' . $k . ')');
                return;
            }
            if (empty($item) || (isset($item['avail']) && !$item['avail'])) {
                continue;
            }
            if (!isset($item['from']) || !isset($item['to']) || !is_string($item['from']) || !is_string($item['to'])) {
                $buildMessage('Неверный формат перерыва в расписании (элемент: ' . $k . ')');
                return;
            }

            $dateFrom = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $item['from']);
            $dateTo = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $item['to']);

            if (
                !($dateFrom instanceof \DateTime) || !($dateTo instanceof \DateTime) ||
                $dateFrom->getTimestamp() >= $dateTo->getTimestamp()
            ) {
                $buildMessage('Неверный формат перерыва в расписании (элемент: ' . $k . ')');
                return;
            }
        }
    }

    /**
     * Валидация праздничных дней
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     * @param mixed $payload
     */
    public function validateHolidays(ExecutionContextInterface $context, $payload)
    {
        $holidays = $this->getHolidays();

        if (empty($holidays)) {
            return;
        }

        $buildMessage = function(string $message) use ($context) {
            $context->buildViolation($message)
                ->atPath('holidays')
                ->addViolation();
        };

        if (!is_array($holidays)) {
            $buildMessage('Неверный формат праздничных дней');
            return;
        }

        foreach ($holidays as $item) {
            if (!is_string($item)) {
                $buildMessage('Неверный формат даты');
                return;
            }

            $date = \DateTime::createFromFormat('Y-m-d', $item);
            if (!($date instanceof \DateTime)) {
                $buildMessage('Неверный формат даты: ' . $item);
                return;
            }
        }
    }

    /**
     * Валидация рабочих выходных дней
     *
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     * @param mixed $payload
     */
    public function validateWorkWeekends(ExecutionContextInterface $context, $payload)
    {
        $workWeekends = $this->getWorkWeekends();

        if (empty($workWeekends)) {
            return;
        }

        $buildMessage = function(string $message) use ($context) {
            $context->buildViolation($message)
                ->atPath('holidays')
                ->addViolation();
        };

        if (!is_array($workWeekends)) {
            $buildMessage('Неверный формат перенесённых выходных');
            return;
        }

        foreach ($workWeekends as $item) {
            if (!is_string($item)) {
                $buildMessage('Неверный формат даты');
                return;
            }

            $date = \DateTime::createFromFormat('Y-m-d', $item);
            if (!($date instanceof \DateTime)) {
                $buildMessage('Неверный формат даты: ' . $item);
                return;
            }
        }
    }

    /**
     * Сериализация объекта в JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        // формирование расписания
        $schedule = $this->getSchedule();
        if (count($schedule) < 7) {
            while(count($schedule) < 7) {
                $schedule[] = [
                    'avail' => true,
                    'from' => '00:00',
                    'to' => '24:00'
                ];
            }
        }

        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'address' => $this->getAddress(),
            'hourlyCost' => $this->getHourlyCost(),
            'schedule' => $this->getSchedule() ?: [],
            'scheduleBreak' => $this->getScheduleBreak() ?: [],
            'holidays' => $this->getHolidays() ?: [],
            'workWeekends' => $this->getWorkWeekends() ?: [],
            'requestPause' => $this->getRequestPause(),
        ];
    }

    /**
     * Проверка доступности даты для регистрации заявки
     *
     * @param \DateTime $date
     *
     * @return bool
     */
    public function checkDayIsAvailable(\DateTime $date)
    {
        $dateFormatted = $date->format('Y-m-d');

        if (is_array($this->workWeekends) && in_array($dateFormatted, $this->workWeekends)) {
            // рабочий выходной - целый день
            return true;
        }

        if (is_array($this->holidays) && in_array($dateFormatted, $this->holidays)) {
            // праздничный день
            return true;
        }

        // проверка на доступность в обычный день
        // индексация: 0 - понедельник, 6 - воскресение
        $weekDay = (int) $date->format('w');
        $weekDay = $weekDay == 0 ? 6 : $weekDay - 1;

        return !is_array($this->schedule) ||
            !isset($this->schedule[$weekDay]) ||
            (
                !empty($this->schedule[$weekDay]['avail']) &&
                $this->schedule[$weekDay]['avail'] == true
            );
    }

    /**
     * Возвращает false, если переговорка недоступна на указанное время
     *
     * @param \DateTime $timeFrom
     * @param \DateTime $timeTo
     *
     * @return bool
     */
    public function checkTimeIsAvailable(\DateTime $timeFrom, \DateTime $timeTo)
    {
        // день недельи
        // индексация: 0 - понедельник, 6 - воскресение
        $weekDay = (int) $timeFrom->format('w');
        $weekDay = $weekDay == 0 ? 6 : $weekDay - 1;

        $from = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $timeFrom->format('H:i'));
        $to = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $timeTo->format('H:i'));

        if (is_array($this->scheduleBreak)) {
            // проверка перерывов
            foreach ($this->scheduleBreak as $scheduleBreak) {
                if (!isset($scheduleBreak['from']) || !isset($scheduleBreak['to'])) {
                    continue;
                }
                $checkFrom = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $scheduleBreak['from']);
                $checkTo = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $scheduleBreak['to']);
                if (!$checkFrom || !$checkTo) {
                    continue;
                }

                if (
                    ($from->getTimestamp() <= $checkFrom->getTimestamp() && $to->getTimestamp() >= $checkTo->getTimestamp()) ||
                    ($from->getTimestamp() >= $checkFrom->getTimestamp() && $from->getTimestamp() <= $checkTo->getTimestamp()) ||
                    ($to->getTimestamp() >= $checkFrom->getTimestamp() && $to->getTimestamp() <= $checkTo->getTimestamp())
                ) {
                    return false;
                }
            }
        }

        if (is_array($this->schedule) && isset($this->schedule[$weekDay]) && $this->schedule[$weekDay]['avail'] && !empty($this->schedule[$weekDay]['schedule'])) {
            // проверка рабочего времени
            foreach ($this->schedule[$weekDay]['schedule'] as $schedule) {
                if (!isset($schedule['from']) || !isset($schedule['to'])) {
                    continue;
                }
                $checkFrom = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $schedule['from']);
                $checkTo = \DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d ') . $schedule['to']);
                if (!$checkFrom || !$checkTo) {
                    continue;
                }

                if ($from->getTimestamp() < $checkFrom->getTimestamp() || $to->getTimestamp() > $checkTo->getTimestamp()) {
                    return false;
                }
            }
        }

        return true;
    }
}
