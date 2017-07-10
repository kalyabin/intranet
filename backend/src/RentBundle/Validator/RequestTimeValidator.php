<?php

namespace RentBundle\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use RentBundle\Entity\Repository\RoomRequestRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Validator\Constraints\RequestTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Валидация времени заявки.
 *
 * Запрещает регистрацию заявки на недоступное время в календаре.
 *
 * @package RentBundle\Validator
 */
class RequestTimeValidator extends ConstraintValidator
{
    /**
     * @var RoomRequestRepository
     */
    protected $requestRepository;

    public function __construct(ObjectManager $em)
    {
        $this->requestRepository = $em->getRepository(RoomRequestEntity::class);
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var RequestTime $constraint */

        $object = $this->context->getObject();

        /** @var \DateTime $timeFrom */
        $timeFrom = $object->{$constraint->timeFromCallback}();
        /** @var \DateTime $timeTo */
        $timeTo = $object->{$constraint->timeToCallback}();
        /** @var RoomEntity $room */
        $room = $object->{$constraint->roomCallback}();

        // без указанных сущностей валидация невозможна
        if (!$timeFrom || !$timeTo || !$room) {
            return;
        }

        if (!$room->checkDayIsAvailable($timeFrom)) {
            $this->context->addViolation('Помещение недоступно на дату ' . $timeFrom->format('d.m.Y'));
        }

        if (!$room->checkDayIsAvailable($timeTo)) {
            $this->context->addViolation('Помещение недоступно на дату ' . $timeTo->format('d.m.Y'));
        }

        if (!$room->checkTimeIsAvailable($timeFrom, $timeTo)) {
            $this->context->addViolation('Помещение недоступно на время ' . $timeFrom->format('H:i') . ' - ' . $timeTo->format('H:i'));
        }
    }
}
