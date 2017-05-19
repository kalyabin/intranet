<?php

namespace TicketBundle\Tests\Entity;


use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование тикетов
 *
 * @package TicketBundle\Tests\Entity
 */
class TicketEntityTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();
    }

    /**
     * @covers TicketEntity::getId()
     * @covers TicketEntity::getNumber()
     * @covers TicketEntity::getTitle()
     * @covers TicketEntity::getCreatedAt()
     * @covers TicketEntity::getManagedBy()
     * @covers TicketEntity::getLastQuestionAt()
     * @covers TicketEntity::getLastAnswerAt()
     * @covers TicketEntity::getCurrentStatus()
     * @covers TicketEntity::getCreatedBy()
     * @covers TicketEntity::getCustomer()
     * @covers TicketEntity::getVoidedAt()
     *
     * @covers TicketEntity::setNumber()
     * @covers TicketEntity::setTitle()
     * @covers TicketEntity::setCreatedAt()
     * @covers TicketEntity::setManagedBy()
     * @covers TicketEntity::setLastQuestionAt()
     * @covers TicketEntity::setLastAnswerAt()
     * @covers TicketEntity::setCurrentStatus()
     * @covers TicketEntity::setCreatedBy()
     * @covers TicketEntity::setCustomer()
     * @covers TicketEntity::setVoidedAt()
     */
    public function testMe()
    {
        /** @var UserEntity $userManager */
        $userManager = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $userCustomer */
        $userCustomer = $this->fixtures->getReference('ticket-customer-user');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('ticket-customer');
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $entity = new TicketEntity();

        $this->assertNull($entity->getNumber());
        $this->assertNull($entity->getTitle());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getManagedBy());
        $this->assertNull($entity->getLastQuestionAt());
        $this->assertNull($entity->getLastAnswerAt());
        $this->assertNull($entity->getCurrentStatus());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getCustomer());
        $this->assertNull($entity->getVoidedAt());

        $entity
            ->setTitle('testing ticket')
            ->setNumber('testing number')
            ->setCreatedAt(new \DateTime())
            ->setCategory($category)
            ->setManagedBy($userManager)
            ->setLastQuestionAt(new \DateTime())
            ->setLastAnswerAt(new \DateTime())
            ->setCurrentStatus(TicketEntity::STATUS_NEW)
            ->setCreatedBy($userCustomer)
            ->setCustomer($customer)
            ->setVoidedAt(new \DateTime());

        $this->assertEquals('testing ticket', $entity->getTitle());
        $this->assertEquals('testing number', $entity->getNumber());
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $entity->getManagedBy());
        $this->assertInstanceOf(\DateTime::class, $entity->getLastQuestionAt());
        $this->assertInstanceOf(\DateTime::class, $entity->getLastAnswerAt());
        $this->assertEquals(TicketEntity::STATUS_NEW, $entity->getCurrentStatus());
        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());
        $this->assertInstanceOf(CustomerEntity::class, $entity->getCustomer());
        $this->assertInstanceOf(\DateTime::class, $entity->getVoidedAt());

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $em->persist($customer);
        $em->persist($userCustomer);
        $em->persist($entity);

        $em->flush();
    }
}
