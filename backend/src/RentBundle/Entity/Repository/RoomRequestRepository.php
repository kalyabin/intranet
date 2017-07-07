<?php

namespace RentBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
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
     * Получить все актуальне заявки для всех помещений.
     * Актуальными считаются заявки, которые будут в будущем или не старше 2-х месяцев.
     *
     * @return RoomRequestEntity[]
     */
    public function findAllActual(): array
    {
        // выводить заявки не старше 2-х месяцев
        // старше скорее всего не актуальны и никого не интересуют
        $dateFrom = new \DateTime();
        $dateFrom->sub(new \DateInterval('P2M'));
        $dateFrom->setTime(0, 0, 0);

        return $this->createQueryBuilder('r')
            ->where('r.from >= :from')
            ->setParameters([
                'from' => $dateFrom
            ])
            ->getQuery()
            ->getResult();
    }

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

    /**
     * Получить актуальный список заявок для арендатора
     * Актуальным считается список заявок, которые будут в будущем и не старше 1-го года.
     *
     * @param CustomerEntity $customer
     *
     * @return array
     */
    public function findActualByCustomer(CustomerEntity $customer): array
    {
        // выводить заявки не старше 2-х месяцев
        // старше скорее всего не актуальны и никого не интересуют
        $dateFrom = new \DateTime();
        $dateFrom->sub(new \DateInterval('P1Y'));
        $dateFrom->setTime(0, 0, 0);

        return $this->createQueryBuilder('r')
            ->where('r.customer = :customer and r.from >= :from')
            ->setParameters([
                'customer' => $customer,
                'from' => $dateFrom
            ])
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Поиск заявки по идентификатору и контрагенту
     *
     * @param int $id
     * @param CustomerEntity $customer
     *
     * @return null|RoomRequestEntity
     */
    public function findOneByIdAndCustomer(int $id, CustomerEntity $customer): ?RoomRequestEntity
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :id and r.customer = :customer')
            ->setParameters([
                'id' => $id,
                'customer' => $customer,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить заявку по идентификатору
     *
     * @param int $id
     *
     * @return null|RoomRequestEntity
     */
    public function findOneById(int $id): ?RoomRequestEntity
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameters([
                'id' => $id
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
