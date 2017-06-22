<?php

namespace TicketBundle\Tests\Entity\Repository;

use CustomerBundle\Entity\CustomerEntity;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use Tests\DataFixtures\ORM\TicketTestFixture;
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

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->repository = $objectManager->getRepository(TicketEntity::class);

        $this->entityManager = $objectManager;

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            UserTestFixture::class,
            TicketTestFixture::class,
            TicketCategoryTestFixture::class,
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
        /** @var CustomerEntity $noneCustomer */
        $noneCustomer = $this->fixtures->getReference('none-customer');

        // поиск всех заявок
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter()->getQuery()->getResult();

        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(TicketEntity::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по категории
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter(['not exists category'])->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter([$category->getId()])->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по контрагенту
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter([$category->getId()], $noneCustomer->getId())->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter([$category->getId()], $ticketCustomer->getId())->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());

        // поиск по статусам
        /** @var TicketEntity[] $result */
        $result = $this->repository->findAllByFilter([$category->getId()], $ticketCustomer->getId(), false)->getQuery()->getResult();
        $this->assertEmpty($result);

        $result = $this->repository->findAllByFilter([$category->getId()], $ticketCustomer->getId(), true)->getQuery()->getResult();
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($ticket->getId(), $result[0]->getId());
    }

    /**
     * @depends testRepository
     *
     * @covers TicketRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $result = $this->repository->findOneById($ticket->getId());

        $this->assertInstanceOf(TicketEntity::class, $result);
        $this->assertEquals($result->getId(), $ticket->getId());
    }

    /**
     * @depends testRepository
     *
     * @covers TicketRepository::findNeedToClose()
     */
    public function testFindNeedToClose()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        // сначала пробуем получить с установкой voidedAt = null
        $ticket->setVoidedAt(null);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
        $this->entityManager->clear(TicketEntity::class);
        $result = $this->repository->findNeedToClose();
        $this->assertNotEmpty($result);
        foreach ($result as $items) {
            $this->assertCount(0, $items);
        }

        // устанавливаем вчерашнюю дату как время освобождения и статус = есть вопрос
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $ticket->setCurrentStatus(TicketEntity::STATUS_WAIT);
        $ticket->setVoidedAt($yesterday);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
        $this->entityManager->clear(TicketEntity::class);
        $result = $this->repository->findNeedToClose();
        $this->assertNotEmpty($result);
        foreach ($result as $items) {
            $this->assertCount(0, $items);
        }

        // устанавливаем дату вчерашним днем и статус = получен ответ
        $ticket->setCurrentStatus(TicketEntity::STATUS_ANSWERED);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
        $this->entityManager->clear(TicketEntity::class);
        $result = $this->repository->findNeedToClose();
        $this->assertNotEmpty($result);
        foreach ($result as $items) {
            $this->assertCount(1, $items);
            foreach ($items as $item) {
                /** @var TicketEntity $item */
                $this->assertInstanceOf(TicketEntity::class, $item);
                $this->assertEquals($item->getId(), $ticket->getId());
            }
        }
    }
}
