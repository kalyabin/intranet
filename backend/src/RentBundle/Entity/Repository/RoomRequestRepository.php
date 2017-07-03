<?php

namespace RentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;

/**
 * Репозиторий для заявок на бронирование помещений
 *
 * @package RentBundle\Entity\Repository
 */
class RoomRequestRepository extends EntityRepository
{
    /**
     * Получить актуальный список заявок для помещения.
     * Актуальным считается список заявок, которые будут в будущем и не старше 2-х месяцев.
     *
     * @param RoomEntity $room
     *
     * @return RoomRequestEntity[]
     */
    public function findActualByRoom(RoomEntity $room): array
    {
        // выводить заявки не старше 2-х месяцев
        // старше скорее всего не актуальны и никого не интересуют
        $dateFrom = new \DateTime();
        $dateFrom->sub(new \DateInterval('P2M'));
        $dateFrom->setTime(0, 0, 0);

        return $this->createQueryBuilder('r')
            ->where('r.room = :room and r.from >= :from')
            ->setParameters([
                'room' => $room,
                'from' => $dateFrom
            ])
            ->getQuery()
            ->getResult();
    }
}
