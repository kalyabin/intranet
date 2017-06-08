<?php

namespace TicketBundle\Tests\Entity;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketHistoryEntity;
use Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование истории по тикету
 *
 * @package TicketBundle\Tests\Entity
 */
class TicketHistoryEntityTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            UserTestFixture::class,
            TicketCategoryTestFixture::class,
            TicketTestFixture::class
        ])->getReferenceRepository();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @covers TicketHistoryEntity::getId()
     * @covers TicketHistoryEntity::getCreatedAt()
     * @covers TicketHistoryEntity::getCreatedBy()
     * @covers TicketHistoryEntity::getStatus()
     * @covers TicketHistoryEntity::getTicket()
     *
     * @covers TicketHistoryEntity::setCreatedAt()
     * @covers TicketHistoryEntity::setCreatedBy()
     * @covers TicketHistoryEntity::setTicket()
     * @covers TicketHistoryEntity::setStatus()
     */
    public function testMe()
    {
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $entity = new TicketHistoryEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getStatus());
        $this->assertNull($entity->getTicket());

        $entity
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($customerUser)
            ->setTicket($ticket)
            ->setStatus(TicketEntity::STATUS_CLOSED);

        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());
        $this->assertInstanceOf(TicketEntity::class, $entity->getTicket());
        $this->assertEquals(TicketEntity::STATUS_CLOSED, $entity->getStatus());

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());
    }
}
