<?php

namespace CustomerBundle\Tests\Entity\Repository;

use CustomerBundle\Entity\Repository\ServiceRepository;
use CustomerBundle\Entity\ServiceEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;

class ServiceRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var ServiceRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([ServiceTestFixture::class])->getReferenceRepository();
        $this->repository = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(ServiceEntity::class);
        $this->assertInstanceOf(ServiceRepository::class, $this->repository);
    }

    /**
     * @covers ServiceRepository::findAll()
     */
    public function testFindAll()
    {
        // получить идентификаторы всех услуг
        $allIds = [];
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof ServiceEntity) {
                $allIds[] = $reference->getId();
            }
        }

        $list = $this->repository->findAll();

        $this->assertCount(count($allIds), $list);

        foreach ($list as $item) {
            $this->assertTrue(in_array($item->getId(), $allIds));
        }
    }

    /**
     * @covers ServiceRepository::findAllActive()
     */
    public function testFindAllActive()
    {
        // получить идентификаторы всех активных услуг
        $allActiveIds = [];
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof ServiceEntity && $reference->getIsActive()) {
                $allActiveIds[] = $reference->getId();
            }
        }

        $list = $this->repository->findAllActive();

        $this->assertCount(count($allActiveIds), $list);

        foreach ($list as $item) {
            $this->assertTrue(in_array($item->getId(), $allActiveIds));
        }
    }

    /**
     * @covers ServiceRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var ServiceEntity $expected */
        $expected = $this->fixtures->getReference('service-it');

        $item = $this->repository->findOneById($expected->getId());

        $this->assertInstanceOf(ServiceEntity::class, $item);
        $this->assertEquals($item->getId(), $expected->getId());

        $this->assertNull($this->repository->findOneById('unexpected-service'));
    }
}
