<?php

namespace TicketBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\UserEntity;

/**
 * Репозиторий для работы с тикетами
 *
 * @package TicketBundle\Entity\Repository
 */
class TicketRepository extends EntityRepository
{
    /**
     * Получить общее количество тикетов для конкретного контрагента
     *
     * @param CustomerEntity $customer
     *
     * @return int
     */
    public function getTotalCountByCustomer(CustomerEntity $customer): int
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return (int) $queryBuilder->select($queryBuilder->expr()->count('t.id'))
            ->where('t.customer = :customerId')
            ->setParameter('customerId', $customer->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Получить общее количество тикетов для конкретного пользователя
     *
     * @param UserEntity $user
     *
     * @return int
     */
    public function getTotalCountByUser(UserEntity $user): int
    {
        $queryBuilder = $this->createQueryBuilder('t');

        return (int) $queryBuilder->select($queryBuilder->expr()->count('t.id'))
            ->where('t.createdBy = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
