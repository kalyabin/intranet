<?php

namespace TicketBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use TicketBundle\Entity\TicketEntity;
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

    /**
     * Получить все заявки по фильтру
     *
     * @param string|null $category Категоряи по которой искать (по умолчанию - по всем)
     * @param int|null $customer Контрагент по которому искать (по умолчанию - по всем)
     * @param bool|null $opened Статус заявки - открыта (по умолчанию - искать по всем открытым)
     *
     * @return QueryBuilder
     */
    public function findAllByFilter(?string $category = null, ?int $customer = null, ?bool $opened = true): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if ($category) {
            // фильтр по категории
            $queryBuilder
                ->andWhere('t.category = :category')
                ->setParameter('category', $category);
        }

        if ($opened) {
            // фильтр по открым заявкам
            $queryBuilder
                ->andWhere('t.currentStatus <> :status')
                ->setParameter('status', TicketEntity::STATUS_CLOSED);
        } else {
            // фильтр по закрытым заявкам
            $queryBuilder
                ->andWhere('t.currentStatus = :status')
                ->setParameter('status', TicketEntity::STATUS_CLOSED);

            // не старше одного года
            $dateTime = new \DateTime();
            $dateTime->sub(new \DateInterval('P1Y'));

            $queryBuilder
                ->andWhere('t.voidedAt = :voidedAt')
                ->setParameter('voidedAt', $dateTime);
        }

        if ($customer) {
            // фильтр по контрагенту
            $queryBuilder
                ->andWhere('t.customer = :customer')
                ->setParameter('customer', $customer);
        }

        return $queryBuilder;
    }
}
