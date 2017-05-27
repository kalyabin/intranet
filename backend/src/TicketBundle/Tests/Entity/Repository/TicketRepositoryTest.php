<?php

namespace TicketBundle\Tests\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование репозитория заявок
 *
 * @package TicketBundle\Tests\Entity\Repository
 */
class TicketRepositoryTest extends WebTestCase
{
    /**
     * @var TicketRepository
     */
    protected $repository;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->repository = $objectManager->getRepository(TicketEntity::class);

        $this->fixtures = $this->loadFixtures([
            TicketTestFixture::class,
            CustomerTestFixture::class
        ])->getReferenceRepository();
    }

    /**
     * Проверка класса репозитория
     */
    public function testRepository()
    {
        $this->assertInstanceOf(TicketRepository::class, $this->repository);
    }

    /**
     * @depends testRepository
     *
     * @covers TicketRepository::getTotalCountByCustomer()
     */
    public function testGetTotalCountByCustomer()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('ticket-customer');

        $result = $this->repository->getTotalCountByCustomer($customer);

        $this->assertEquals(1, $result);
    }

    /**
     * @depends testRepository
     *
     * @covers TicketRepository::getTotalCountByUser()
     */
    public function testGetTotalCountByUser()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('ticket-customer-user');

        $result = $this->repository->getTotalCountByUser($user);

        $this->assertEquals(1, $result);
    }

    /**
     * @depends testRepository
     *
     * @covers TicketRepository::findAllByFilter()
     */
    public function testFindAllByFilter()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var CustomerEntity $ticketCustomer */
        $ticketCustomer = $this->fixtures->getReference('ticket-customer');
        /** @var CustomerEntity $allCustomer */
        $allCustomer = $this->fixtures->getReference('all-customer');

        // поиск всех заявок
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter()->getQuery()->getResult();

        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(TicketEntity::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по категории
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter('not exists category')->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter($category->getId())->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по контрагенту
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter($category->getId(), $allCustomer->getId())->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter($category->getId(), $ticketCustomer->getId())->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по статусам
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter($category->getId(), $ticketCustomer->getId(), false)->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter($category->getId(), $ticketCustomer->getId(), true)->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());
    }

}
