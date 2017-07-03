<?php

namespace RentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RentBundle\Entity\RoomEntity;

/**
 * Репозиторий для помещений
 *
 * @package RentBundle\Entity\Repository
 */
class RoomRepository extends EntityRepository
{
    /**
     * Получить все комнаты
     *
     * @return RoomEntity[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить помещение по идентификатору
     *
     * @param int $id
     *
     * @return null|RoomEntity
     */
    public function findOneById(int $id): ?RoomEntity
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
