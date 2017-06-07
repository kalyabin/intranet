<?php

namespace AppBundle\Tests\Entity;


use AppBundle\Entity\UserNotificationEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование системных уведомлений для пользователей
 *
 * @package AppBundle\Tests\Entity
 */
class UserNotificationEntityTest extends WebTestCase
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
            UserTestFixture::class,
            TicketTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers UserNotificationEntity::getId()
     * @covers UserNotificationEntity::getCreatedAt()
     * @covers UserNotificationEntity::getType()
     * @covers UserNotificationEntity::getReceiver()
     * @covers UserNotificationEntity::getAuthor()
     * @covers UserNotificationEntity::getTicket()
     * @covers UserNotificationEntity::getTicketMessage()
     * @covers UserNotificationEntity::getTicketManager()
     *
     * @covers UserNotificationEntity::setCreatedAt()
     * @covers UserNotificationEntity::setType()
     * @covers UserNotificationEntity::setReceiver()
     * @covers UserNotificationEntity::setAuthor()
     * @covers UserNotificationEntity::setTicket()
     * @covers UserNotificationEntity::setTicketMessage()
     * @covers UserNotificationEntity::setTicketManager()
     */
    public function testMe()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');
        /** @var UserEntity $superadmin */
        $superadmin = $this->fixtures->getReference('superadmin-user');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var TicketMessageEntity $ticketMessage */
        $ticketMessage = $this->fixtures->getReference('ticket-message');

        $entity = new UserNotificationEntity();

        // основные поля
        $this->assertNull($entity->getId());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getType());
        $this->assertNull($entity->getReceiver());

        // дополнительные поля
        $this->assertNull($entity->getAuthor());
        $this->assertNull($entity->getTicket());
        $this->assertNull($entity->getTicketMessage());
        $this->assertNull($entity->getTicketManager());

        // уведомление без дополнительных полей должно свободно создаваться
        $entity
            ->setCreatedAt(new \DateTime())
            ->setType(UserNotificationEntity::TYPE_TICKET_NEW)
            ->setReceiver($user);

        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertEquals(UserNotificationEntity::TYPE_TICKET_NEW, $entity->getType());
        $this->assertInstanceOf(UserEntity::class, $entity->getReceiver());
        $this->assertEquals($user->getId(), $entity->getReceiver()->getId());

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());

        // далее установка дополнительных полей
        $entity->setAuthor($superadmin);
        $entity->setTicket($ticket);
        $entity->setTicketMessage($ticketMessage);
        $entity->setTicketManager($superadmin);

        $this->assertInstanceOf(UserEntity::class, $entity->getAuthor());
        $this->assertEquals($superadmin->getId(), $entity->getAuthor()->getId());
        $this->assertInstanceOf(TicketEntity::class, $entity->getTicket());
        $this->assertEquals($ticket->getId(), $entity->getTicket()->getId());
        $this->assertInstanceOf(TicketMessageEntity::class, $entity->getTicketMessage());
        $this->assertEquals($ticketMessage->getId(), $entity->getTicketMessage()->getId());
        $this->assertInstanceOf(UserEntity::class, $entity->getTicketManager());
        $this->assertEquals($superadmin->getId(), $entity->getTicketManager()->getId());

        $this->entityManager->persist($entity);

        $this->entityManager->flush();
    }
}
