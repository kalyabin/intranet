<?php

namespace TicketBundle\Tests\Entity\Repository;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\Repository\TicketCategoryRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;

/**
 * Тестирование TicketCategoryRepository
 *
 * @package TicketBundle\Tests\Entity\Repository
 */
class TicketCategoryRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var TicketCategoryRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $em->getRepository(TicketCategoryEntity::class);

        $this->assertInstanceOf(TicketCategoryRepository::class, $this->repository);
    }

    /**
     * @covers TicketCategoryRepository::findByManagerRoles()
     */
    public function testFindByManagerRoles()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        /** @var TicketCategoryEntity[] $result */
        $result = $this->repository->findByManagerRoles([$category->getManagerRole()]);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(TicketCategoryEntity::class, $result);
        $this->assertEquals($category->getId(), $result[0]->getId());
    }

    /**
     * @covers TicketCategoryRepository::findByCustomerRoles()
     */
    public function testFindByCustomerRoles()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        /** @var TicketCategoryEntity[] $result */
        $result = $this->repository->findByCustomerRoles([$category->getCustomerRole()]);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(TicketCategoryEntity::class, $result);
        $this->assertEquals($category->getId(), $result[0]->getId());
    }

    /**
     * @covers TicketCategoryRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $result = $this->repository->findOneById($category->getId());

        $this->assertInstanceOf(TicketCategoryEntity::class, $result);
        $this->assertEquals($result->getId(), $category->getId());

        // поиск несуществующей категории
        $result = $this->repository->findOneById('not existent category');

        $this->assertNull($result);
    }
}
