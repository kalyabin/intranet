<?php

namespace TicketBundle\Tests\Entity;


use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\TicketCategoryEntity;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;

/**
 * Тестирование категорий (очередей) тикетной системы
 *
 * @package TicketBundle\Tests\Entity
 */
class TicketCategoryEntityTest extends WebTestCase
{
    /**
     * @var ObjectManager
     */
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures([TicketCategoryTestFixture::class]);
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @covers TicketCategoryEntity::setId()
     * @covers TicketCategoryEntity::setName()
     * @covers TicketCategoryEntity::setCustomerRole()
     * @covers TicketCategoryEntity::setManagerRole()
     *
     * @covers TicketCategoryEntity::getId()
     * @covers TicketCategoryEntity::getName()
     * @covers TicketCategoryEntity::getCustomerRole()
     * @covers TicketCategoryEntity::getManagerRole()
     */
    public function testMe()
    {
        $entity = new TicketCategoryEntity();

        $id = 'testing';
        $name = 'IT-аутсорсинг';
        $managerRole = 'ROLE_IT_MANAGER';
        $customerRole = 'ROLE_IT_CUSTOMER';

        $entity
            ->setId($id)
            ->setName($name)
            ->setManagerRole($managerRole)
            ->setCustomerRole($customerRole);

        $this->assertEquals($entity->getId(), $id);
        $this->assertEquals($entity->getName(), $name);
        $this->assertEquals($entity->getManagerRole(), $managerRole);
        $this->assertEquals($entity->getCustomerRole(), $customerRole);

        $this->em->persist($entity);

        $this->em->flush();
    }
}
