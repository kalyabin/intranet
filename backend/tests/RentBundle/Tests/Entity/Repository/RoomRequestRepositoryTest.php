<?php

namespace RentBundle\Tests\Entity\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Entity\Repository\RoomRequestRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomRequestTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;

/**
 * Репозиторий заявок на бронирование помещений
 *
 * @package RentBundle\Tests\Entity\Repository
 */
class RoomRequestRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var RoomRequestRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            RoomTestFixture::class,
            RoomRequestTestFixture::class
        ])->getReferenceRepository();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $entityManager->getRepository(RoomRequestEntity::class);

        $this->assertInstanceOf(RoomRequestRepository::class, $this->repository);
    }

    /**
     * @covers RoomRequestRepository::findActualByRoom()
     */
    public function testFindActualByRoom()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var RoomRequestEntity $expectedRequest */
        $expectedRequest = $this->fixtures->getReference('all-customer-everyday-room-request');
        /** @var RoomRequestEntity $unexpectedRequest */
        $unexpectedRequest = $this->fixtures->getReference('all-customer-everyday-room-old-request');

        $list = $this->repository->findActualByRoom($room);

        $this->assertContainsOnlyInstancesOf(RoomRequestEntity::class, $list);
        $this->assertCount(1, $list);
        $this->assertEquals($list[0]->getId(), $expectedRequest->getId());
    }
}
