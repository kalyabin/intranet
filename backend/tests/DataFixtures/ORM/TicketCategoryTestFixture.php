<?php

namespace Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TicketBundle\Entity\TicketCategoryEntity;

/**
 * Фикстуры категорий тикетной системы
 *
 * @package TicketBundle\Tests\DataFixtures\ORM
 */
class TicketCategoryTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $entity = new TicketCategoryEntity();

        $entity
            ->setId('it-department')
            ->setName('IT-аутсорсинг')
            ->setManagerRole('ROLE_IT_MANAGEMENT')
            ->setCustomerRole('ROLE_IT_CUSTOMER');

        $manager->persist($entity);

        $manager->flush();

        $this->addReference('it-department', $entity);
    }

    /**
     * Категория тикетной системы ни отчего не зависит
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
