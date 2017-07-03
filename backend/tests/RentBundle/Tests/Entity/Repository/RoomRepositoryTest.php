<?php

namespace RentBundle\Tests\Entity\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Entity\Repository\RoomRepository;
use RentBundle\Entity\RoomEntity;
use Tests\DataFixtures\ORM\RoomTestFixture;

/**
 * Тестирование репозитория для помещений
 *
 * @package RentBundle\Tests\Entity\Repository
 */
class RoomRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var RoomRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            RoomTestFixture::class
        ])->getReferenceRepository();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->repository = $entityManager->getRepository(RoomEntity::class);

        $this->assertInstanceOf(RoomRepository::class, $this->repository);
    }

    /**
     * @covers RoomRepository::findAll()
     */
    public function testFindAll()
    {
        $roomsCnt = 0;
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof RoomEntity) {
                $roomsCnt++;
            }
        }

        $result = $this->repository->findAll();

        $this->assertInternalType('array', $result);
        $this->assertCount($roomsCnt, $result);
        $this->assertContainsOnlyInstancesOf(RoomEntity::class, $result);
    }

    /**
     * @covers RoomRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var RoomEntity $entity */
        $entity = $this->fixtures->getReference('everyday-room');

        $result = $this->repository->findOneById($entity->getId());

        $this->assertInstanceOf(RoomEntity::class, $result);
        $this->assertEquals($result->getId(), $entity->getId());

        $result = $this->repository->findOneById(-1);
        $this->assertNull($result);
    }
}
