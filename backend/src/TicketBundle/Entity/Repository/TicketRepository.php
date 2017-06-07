<?php

namespace TicketBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
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
     * @param array|null $categories Категории по которой искать (по умолчанию - по всем)
     * @param int|null $customer Контрагент по которому искать (по умолчанию - по всем)
     * @param bool|null $opened Статус заявки - открыта (по умолчанию - искать по всем открытым)
     *
     * @return QueryBuilder
     */
    public function findAllByFilter(?array $categories = null, ?int $customer = null, ?bool $opened = true): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if (!empty($categories)) {
            // фильтр по категории
            $queryBuilder
                ->andWhere('t.category IN(:categories)')
                ->setParameter('categories', $categories);
        }

        if ($opened) {
            // фильтр по открым заявкам
            // открыте либо по статусу "не закрыта", либо по дате закрытия - не старше 1 недели
            $lastQuestionAt = new \DateTime();
            $lastQuestionAt->add(new \DateInterval('P7D'));

            $queryBuilder
                ->andWhere('(t.currentStatus <> :status OR t.lastQuestionAt <= :lastQuestionAt)')
                ->setParameter('status', TicketEntity::STATUS_CLOSED)
                ->setParameter('lastQuestionAt', $lastQuestionAt);
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

    /**
     * Поиск тикета по идентификатору и категории
     *
     * @param int $id
     * @param string $category
     *
     * @return null|TicketEntity
     */
    public function findOneByIdAndCategory(int $id, string $category): ?TicketEntity
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id and t.category = :category')
            ->setParameters([
                'id' => $id,
                'category' => $category
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Поиск тикета по идентификатору
     *
     * @param int $id
     *
     * @return null|TicketEntity
     */
    public function findOneById(int $id): ?TicketEntity
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить тикеты, которые требуют автоматического закрытия
     *
     * @return IterableResult
     */
    public function findNeedToClose(): IterableResult
    {
        return $this->createQueryBuilder('t')
            ->where('t.voidedAt IS NOT NULL')
            ->andWhere('t.voidedAt < :dateTime')
            ->andWhere('t.currentStatus = :status')
            ->setParameters([
                'dateTime' => new \DateTime(),
                'status' => TicketEntity::STATUS_ANSWERED
            ])
            ->getQuery()
            ->iterate();
    }
}
