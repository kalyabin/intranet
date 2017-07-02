<?php

namespace RentBundle\Tests;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Тестирование модели заявки на аренду
 *
 * @package RentBundle\Tests
 */
class RoomRequestEntityTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->fixtures = $this->loadFixtures([
            RoomTestFixture::class,
            ServiceTestFixture::class,
            CustomerTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers RoomRequestEntity::getId()
     * @covers RoomRequestEntity::getCustomer()
     * @covers RoomRequestEntity::getRoom()
     * @covers RoomRequestEntity::getCreatedAt()
     * @covers RoomRequestEntity::getStatus()
     * @covers RoomRequestEntity::getFrom()
     * @covers RoomRequestEntity::getTo()
     * @covers RoomRequestEntity::getManagerComment()
     * @covers RoomRequestEntity::getCustomerComment()
     *
     * @covers RoomRequestEntity::setCustomer()
     * @covers RoomRequestEntity::setRoom()
     * @covers RoomRequestEntity::setCreatedAt()
     * @covers RoomRequestEntity::setStatus()
     * @covers RoomRequestEntity::setFrom()
     * @covers RoomRequestEntity::setTo()
     * @covers RoomRequestEntity::setManagerComment()
     * @covers RoomRequestEntity::setCustomerComment()
     */
    public function testMe()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');
        $from = (new \DateTime())->add(new \DateInterval('P1D'));
        $to = (new \DateTime())->add(new \DateInterval('P2D'));
        $managerComment = 'testing manager comment';
        $customerComment = 'testing customer comment';

        $entity = new RoomRequestEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getCustomer());
        $this->assertNull($entity->getRoom());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getStatus());
        $this->assertNull($entity->getFrom());
        $this->assertNull($entity->getTo());
        $this->assertNull($entity->getManagerComment());
        $this->assertNull($entity->getCustomerComment());

        $entity
            ->setCustomer($customer)
            ->setRoom($room)
            ->setCreatedAt(new \DateTime())
            ->setStatus(RoomRequestEntity::STATUS_APPROVED)
            ->setFrom($from)
            ->setTo($to)
            ->setManagerComment($managerComment)
            ->setCustomerComment($customerComment);

        $this->assertInstanceOf(CustomerEntity::class, $entity->getCustomer());
        $this->assertEquals($customer->getId(), $entity->getCustomer()->getId());
        $this->assertInstanceOf(RoomEntity::class, $entity->getRoom());
        $this->assertEquals($room->getId(), $entity->getRoom()->getId());
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertEquals(RoomRequestEntity::STATUS_APPROVED, $entity->getStatus());
        $this->assertInstanceOf(\DateTime::class, $entity->getFrom());
        $this->assertInstanceOf(\DateTime::class, $entity->getTo());
        $this->assertEquals($managerComment, $entity->getManagerComment());
        $this->assertEquals($customerComment, $entity->getCustomerComment());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
