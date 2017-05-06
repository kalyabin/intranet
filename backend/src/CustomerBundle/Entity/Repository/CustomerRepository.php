<?php

namespace CustomerBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityRepository;

/**
 * Класс-репозиторий для контрагентов
 *
 * @package CustomerBundle\Entity\Repository
 */
class CustomerRepository extends EntityRepository
{
    /**
     * Получить общее количество элементов
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        $queryBuilder = $this->createQueryBuilder('c');

        return (int) $queryBuilder->select($queryBuilder->expr()->count('c.id'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Поиск контрагента по идентификатору
     *
     * @param int $id
     *
     * @return CustomerEntity|null
     */
    public function findOneById(int $id): ?CustomerEntity
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
