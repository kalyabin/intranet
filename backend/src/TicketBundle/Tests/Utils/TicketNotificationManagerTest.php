<?php

namespace TicketBundle\Tests\Utils;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\MailManagerTestTrait;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketClosedEvent;
use TicketBundle\Event\TicketManagerSetEvent;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use TicketBundle\Event\TicketUserNotificationEvent;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use TicketBundle\Utils\TicketNotificationManager;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Тестирование уведомлений по заявкам
 *
 * @package TicketBundle\Tests\Utils
 */
class TicketNotificationManagerTest extends WebTestCase
{
    use MailManagerTestTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var TicketNotificationManager
     */
    protected $manager;

    /**
     * @var int[] id пользователей получателей
     */
    protected $receiverIds = [];

    /**
     * @var int Количество сгенерированных уведомлений
     */
    protected $eventCnt = 0;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var RolesManager $rolesManager */
        $rolesManager = $this->getContainer()->get('user.roles_manager');
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->manager = new TicketNotificationManager($em, $rolesManager, $eventDispatcher);
        $self = $this;
        $eventDispatcher->addListener('user_notification', function($event) use ($self) {
            if ($event instanceof TicketUserNotificationEvent) {
                $self->receiverIds[] = $event->getReceiver()->getId();
                $self->eventCnt++;
            }
        });
        $this->receiverIds = [];
        $this->eventCnt = 0;
        // подписаться на события user_notification для проверки количества сгенерированных уведомлений и емейлов получателей
        $this->clearLastMessage();
    }

    /**
     * Проверка количества сгенерированных уведомлений
     *
     * @param int $cnt
     */
    protected function assertDispatchedUserNotifications(int $cnt)
    {
        $this->assertEquals($cnt, $this->eventCnt);
    }

    /**
     * Проверить, что уведомление было отправлено юзеру
     *
     * @param int $id
     */
    protected function assertDispathcerUserContainsId(int $id)
    {
        $this->assertContains($id, $this->receiverIds);
    }

    /**
     * @covers TicketNotificationManager::onNewTicket()
     */
    public function testOnNewTicket()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-message');
        /** @var UserEntity $manager */
        $manager = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager-other');

        $event = new TicketNewEvent($ticket, $message);
        $this->manager->onNewTicket($event);

        $this->assertDispatchedUserNotifications(3);
        $this->assertDispathcerUserContainsId($manager->getId());
        $this->assertDispathcerUserContainsId($managerOther->getId());
        $this->assertDispathcerUserContainsId($ticket->getCreatedBy()->getId());

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketNotificationManager::onNewAnswer()
     */
    public function testOnNewAnswer()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-answer');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('ticket-customer-user');

        $event = new TicketNewMessageEvent($ticket, $message);
        $this->manager->onNewAnswer($event);

        $this->assertDispatchedUserNotifications(1);
        $this->assertDispathcerUserContainsId($user->getId());

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketNotificationManager::onNewQuestion()
     */
    public function testOnNewQuestion()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $manager */
        $manager = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager-other');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-answer');

        // сбросить менеджера в тикете
        // сообщения должны уходить всем менеджерам
        $ticket->setManagedBy(null);

        $event = new TicketNewMessageEvent($ticket, $message);
        $result = $this->manager->onNewQuestion($event);

        $this->assertDispatchedUserNotifications(2);
        $this->assertDispathcerUserContainsId($managerOther->getId());
        $this->assertDispathcerUserContainsId($manager->getId());

        $this->assertEquals(2, $result);
        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());

        $this->clearLastMessage();

        // установить другого менеджера, сообщение должно уйти только ему
        $ticket->setManagedBy($managerOther);

        $event = new TicketNewMessageEvent($ticket, $message);
        $result = $this->manager->onNewQuestion($event);

        $this->assertEquals(1, $result);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketNotificationManager::onManagerSet()
     */
    public function testOnManagerSet()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');

        $event = new TicketManagerSetEvent($ticket, $managerOther);
        $result = $this->manager->onManagerSet($event);

        $this->assertDispatchedUserNotifications(1);
        $this->assertDispathcerUserContainsId($customerUser->getId());

        $this->assertEquals(1, $result);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($managerOther->getName());
    }

    /**
     * @covers TicketNotificationManager::onClosedTicket()
     */
    public function testOnClosedTicket()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');

        $event = new TicketClosedEvent($ticket, $customerUser);
        $result = $this->manager->onClosedTicket($event);

        $this->assertDispatchedUserNotifications(1);
        $this->assertDispathcerUserContainsId($customerUser->getId());

        $this->assertEquals(1, $result);

        $this->assertLastMessageContains($ticket->getNumber());
    }
}
