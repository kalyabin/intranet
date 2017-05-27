<?php

namespace TicketBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use TicketBundle\Entity\TicketCategoryEntity;

/**
 * Репозиторий для работы с категориями
 *
 * @package TicketBundle\Entity\Repository
 */
class TicketCategoryRepository extends EntityRepository
{
    /**
     * Поиск категорий по доступным ролям менеджеров
     *
     * @param string[] $managerRoles Роли менеджеров, которые должны присутствовать в категориях
     *
     * @return TicketCategoryEntity[]
     */
    public function findByManagerRoles(array $managerRoles): array
    {
        return $this->createQueryBuilder('c')
            ->distinct()
            ->where('c.managerRole IN (:managerRole)')
            ->setParameter('managerRole', $managerRoles)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Поиска категорий по доступным ролям арендатора
     *
     * @param array $customerRoles
     *
     * @return array
     */
    public function findByCustomerRoles(array $customerRoles): array
    {
        return $this->createQueryBuilder('c')
            ->distinct()
            ->where('c.customerRole IN (:customerRole)')
            ->setParameter('customerRole', $customerRoles)
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить категорию по идентификатору
     *
     * @param string $id
     *
     * @return null|TicketCategoryEntity
     */
    public function findOneById(string $id): ?TicketCategoryEntity
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
