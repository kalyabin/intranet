<?php

namespace TicketBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use TicketBundle\Entity\TicketCategoryEntity;

/**
 * Фикстуры категорий тикетной системы
 *
 * @package TicketBundle\Tests\DataFixtures\ORM
 */
class TicketCategoryTestFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $entity = new TicketCategoryEntity();

        $entity
            ->setId('it-department')
            ->setName('IT-аутсорсинг')
            ->setManagerRole('IT_MANAGEMENT')
            ->setCustomerRole('IT_CUSTOMER');

        $manager->persist($entity);

        $manager->flush();

        $this->addReference('it-department', $entity);
    }
}
