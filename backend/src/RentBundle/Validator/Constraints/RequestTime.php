<?php

namespace RentBundle\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use RentBundle\Validator\RequestTimeValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Валидатор времени заявок.
 *
 * Запрещает привязку заявок к помещению на недоступное время.
 *
 * @Annotation()
 * @Target({"CLASS"})
 *
 * @package RentBundle\Validator\Constraints
 */
class RequestTime extends Constraint
{
    /**
     * @var string callback-функция для получения даты начала действия заявки
     */
    public $timeFromCallback = 'getFrom';

    /**
     * @var string callback-функция для получения даты завершения действия заявки
     */
    public $timeToCallback = 'getTo';

    /**
     * @var string callback-функция для получения комнаты
     */
    public $roomCallback = 'getRoom';

    public function validatedBy()
    {
        return RequestTimeValidator::class;
    }

    public function getTargets()
    {
        return parent::CLASS_CONSTRAINT;
    }
}
