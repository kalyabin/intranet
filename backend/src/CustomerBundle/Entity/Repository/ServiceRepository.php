<?php

namespace CustomerBundle\Entity\Repository;


use CustomerBundle\Entity\ServiceEntity;
use Doctrine\ORM\EntityRepository;

/**
 * Репозиторий для услуг
 *
 * @package CustomerBundle\Entity\Repository
 */
class ServiceRepository extends EntityRepository
{
    /**
     * Получить все услуги, включая которые были отключены (но еще включены в договоры других арендаторов)
     *
     * @return ServiceEntity[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('s')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить все активные услуги
     *
     * @return ServiceEntity[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить услугу по идентификатору
     *
     * @param string $id
     *
     * @return ServiceEntity|null
     */
    public function findOneById($id): ?ServiceEntity
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
