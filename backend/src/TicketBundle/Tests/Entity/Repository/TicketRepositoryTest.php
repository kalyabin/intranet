<?php

namespace TicketBundle\Tests\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\Repository\TicketRepository;
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

        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();
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
}
