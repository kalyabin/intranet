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
            ->where('s.is_active = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }
}
