<?php

namespace CustomerBundle\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceHistoryEntity;
use Doctrine\ORM\EntityRepository;

/**
 * Репозиторий для получения истории активированных услуг
 *
 * @package CustomerBundle\Entity\Repository
 */
class ServiceHistoryRepository extends EntityRepository
{
    /**
     * Получить историю активной услуги на данный момент
     *
     * @param CustomerEntity $customer Арендатор
     * @param ServiceEntity $service Проверка по услуге
     *
     * @return ServiceHistoryEntity[]
     */
    public function findOpenedByCustomer(CustomerEntity $customer, ServiceEntity $service): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.customer = :customer and i.service = :service and i.voidedAt is null')
            ->setParameters([
                'customer' => $customer,
                'service' => $service
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Поиск всех элементов истории для арендатора
     *
     * @param CustomerEntity $customer
     * @param ServiceEntity $service
     *
     * @return array
     */
    public function findAllByCustomer(CustomerEntity $customer, ServiceEntity $service): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.customer = :customer and i.service = :service')
            ->setParameter('service', $service)
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getResult();
    }
}
