<?php

namespace RentBundle\Tests\Utils;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Event\RoomRequestCancelledEvent;
use RentBundle\Event\RoomRequestCreatedEvent;
use RentBundle\Event\RoomRequestUpdatedEvent;
use RentBundle\Utils\RoomRequestManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomRequestTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование заявками в службе аренды
 *
 * @package RentBundle\Tests\Utils
 */
class RoomRequestManagerTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var RoomRequestManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            UserTestFixture::class,
            CustomerTestFixture::class,
            RoomTestFixture::class,
            RoomRequestTestFixture::class
        ])->getReferenceRepository();

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->eventDispatcher = $eventDispatcher;
        $this->manager = new RoomRequestManager($entityManager, $eventDispatcher);
    }

    /**
     * @covers RoomRequestManager::createRequest()
     */
    public function testCreateAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $from = (new \DateTime())->add(new \DateInterval('P1D'));
        $to = (new \DateTime())->add(new \DateInterval('P2D'));
        $managerComment = 'testing manager comment';
        $customerComment = 'testing customer comment';

        $entity = new RoomRequestEntity();

        $entity
            ->setCustomer($customer)
            ->setRoom($room)
            ->setCreatedAt(new \DateTime())
            ->setStatus(RoomRequestEntity::STATUS_APPROVED)
            ->setFrom($from)
            ->setTo($to)
            ->setManagerComment($managerComment)
            ->setCustomerComment($customerComment);

        // подписаться на событие для проверки генерации события
        $eventDispatched = false;
        $this->eventDispatcher->addListener(RoomRequestCreatedEvent::NAME, function($event) use (&$eventDispatched, $room, $customer, $user) {
            /** @var RoomRequestCreatedEvent $event */
            $this->assertInstanceOf(RoomRequestCreatedEvent::class, $event);
            $this->assertInstanceOf(RoomRequestEntity::class, $event->getRequest());
            $this->assertInstanceOf(RoomEntity::class, $event->getRequest()->getRoom());
            $this->assertInstanceOf(CustomerEntity::class, $event->getRequest()->getCustomer());
            $this->assertInstanceOf(UserEntity::class, $event->getAuthor());

            $this->assertEquals($room->getId(), $event->getRequest()->getRoom()->getId());
            $this->assertEquals($customer->getId(), $event->getRequest()->getCustomer()->getId());
            $this->assertEquals($user->getId(), $event->getAuthor()->getId());

            $eventDispatched = true;
        });

        $this->manager->createRequest($entity, $user);

        $this->assertGreaterThan(0, $entity->getId());
        $this->assertTrue($eventDispatched);
    }

    /**
     * @covers RoomRequestManager::cancelRequest()
     */
    public function testCancelAction()
    {
        /** @var RoomRequestEntity $request */
        $request = $this->fixtures->getReference('all-customer-everyday-room-request');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $this->assertNotEquals(RoomRequestEntity::STATUS_CANCELED, $request->getStatus());

        // подписка на событие
        $eventDispatched = false;
        $this->eventDispatcher->addListener(RoomRequestCancelledEvent::NAME, function($event) use (&$eventDispatched, $request, $user) {
            /** @var RoomRequestCancelledEvent $event */
            $this->assertInstanceOf(RoomRequestCancelledEvent::class, $event);
            $this->assertInstanceOf(RoomRequestEntity::class, $event->getRequest());
            $this->assertInstanceOf(UserEntity::class, $event->getAuthor());

            $this->assertEquals($event->getRequest()->getId(), $request->getId());
            $this->assertEquals($event->getAuthor()->getId(), $user->getId());

            $eventDispatched = true;
        });

        $this->manager->cancelRequest($request, $user);

        $this->assertEquals(RoomRequestEntity::STATUS_CANCELED, $request->getStatus());
        $this->assertTrue($eventDispatched);
    }

    /**
     * @covers RoomRequestManager::updateRequestByManager()
     */
    public function testUpdateRequestByManager()
    {
        /** @var RoomRequestEntity $request */
        $request = $this->fixtures->getReference('all-customer-everyday-room-request');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('superadmin-user');

        $oldStatus = $request->getStatus();

        $request
            ->setStatus(RoomRequestEntity::STATUS_DECLINED)
            ->setManagerComment('testing comment');

        // подписка на событие
        $eventDispatched = false;
        $this->eventDispatcher->addListener(RoomRequestUpdatedEvent::NAME, function($event) use (&$eventDispatched, $request, $user) {
            /** @var RoomRequestUpdatedEvent $event */
            $this->assertInstanceOf(RoomRequestUpdatedEvent::class, $event);
            $this->assertInstanceOf(RoomRequestEntity::class, $event->getRequest());
            $this->assertInstanceOf(UserEntity::class, $event->getAuthor());

            $this->assertEquals($event->getRequest()->getId(), $request->getId());
            $this->assertEquals($event->getAuthor()->getId(), $user->getId());

            $eventDispatched = true;
        });

        $this->manager->updateRequestByManager($request, $user, $oldStatus);

        $this->assertTrue($eventDispatched);

        $eventDispatched = false;

        // при неизменном статусе уведомление не должно отсылаться
        $this->manager->updateRequestByManager($request, $user, $request->getStatus());
        $this->assertFalse($eventDispatched);
    }
}
