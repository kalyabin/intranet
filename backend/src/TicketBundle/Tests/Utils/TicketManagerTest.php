<?php

namespace TicketBundle\Tests\Utils;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketHistoryEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketClosedEvent;
use TicketBundle\Event\TicketManagerSetEvent;
use TicketBundle\Event\TicketNewEvent;
use TicketBundle\Event\TicketNewMessageEvent;
use TicketBundle\Form\Type\TicketMessageType;
use TicketBundle\Form\Type\TicketType;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use TicketBundle\Utils\TicketManager;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование менеджера для работы с заявками
 *
 * @package TicketBundle\Tests\Utils
 */
class TicketManagerTest extends WebTestCase
{
    /**
     * @var TicketManager
     */
    protected $manager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get('ticket.manager');
        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();
    }

    /**
     * Проверка последнего статуса тикета
     *
     * @param TicketEntity $ticket
     * @param string $status
     * @param UserEntity|null $author
     */
    protected function assertLastHistoryItem(TicketEntity $ticket, string $status, ?UserEntity $author = null)
    {
        $this->assertNotEmpty($ticket->getHistory());

        /** @var TicketHistoryEntity $lastHistoryItem */
        $lastHistoryItem = $ticket->getHistory()->get($ticket->getHistory()->count() - 1);

        $this->assertInstanceOf(TicketHistoryEntity::class, $lastHistoryItem);
        $this->assertInstanceOf(UserEntity::class, $lastHistoryItem->getCreatedBy());
        if ($author) {
            $this->assertEquals($author->getId(), $lastHistoryItem->getCreatedBy()->getId());
        }
        $this->assertEquals($status, $lastHistoryItem->getStatus());
    }

    /**
     * @covers TicketManager::generateTicketNumber()
     * @covers TicketManager::createTicket()
     */
    public function testCreateTicket()
    {
        /** @var UserEntity $author */
        $author = $this->fixtures->getReference('ticket-customer-user');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('ticket-customer');
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $form = new TicketType();

        $form
            ->setTitle('testing ticket title')
            ->setText('testing ticket text');

        // должно создаться событие
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $testCase = $this;
        $eventTriggered = false;
        $dispatcher->addListener(TicketNewEvent::NAME, function($event) use ($form, $testCase, &$eventTriggered) {
            $this->assertInstanceOf(TicketNewEvent::class, $event);
            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertGreaterThan(0, $event->getTicket()->getId());
            $testCase->assertEquals($form->getTitle(), $event->getTicket()->getTitle());

            $testCase->assertInstanceOf(TicketMessageEntity::class, $event->getMessage());
            $testCase->assertGreaterThan(0, $event->getMessage()->getId());
            $testCase->assertEquals($form->getText(), $event->getMessage()->getText());

            $eventTriggered = true;
        });

        $result = $this->manager->createTicket($form, $category, $author);

        $this->assertTrue($eventTriggered);
        $this->assertInstanceOf(TicketEntity::class, $result);
        $this->assertGreaterThan(0, $result->getId());
        $this->assertNotEmpty($result->getNumber());
        $this->assertEquals(TicketEntity::STATUS_NEW, $result->getCurrentStatus());
        $this->assertInstanceOf(\DateTime::class, $result->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $result->getLastQuestionAt());
        $this->assertNull($result->getLastAnswerAt());
        $this->assertNull($result->getVoidedAt());
        $this->assertInstanceOf(UserEntity::class, $result->getCreatedBy());
        $this->assertEquals($result->getCreatedBy()->getId(), $author->getId());
        $this->assertInstanceOf(CustomerEntity::class, $result->getCustomer());
        $this->assertEquals($result->getCustomer()->getId(), $customer->getId());
        $this->assertEquals($form->getTitle(), $result->getTitle());
        $this->assertLastHistoryItem($result, TicketEntity::STATUS_NEW, $author);

        $this->assertCount(1, $result->getMessage());

        /** @var TicketMessageEntity $message */
        $message = $result->getMessage()[0];

        $this->assertInstanceOf(TicketMessageEntity::class, $message);
        $this->assertGreaterThan(0, $message->getId());
        $this->assertEquals($message->getText(), $form->getText());
        $this->assertInstanceOf(DateTime::class, $message->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $message->getCreatedBy());
        $this->assertEquals($author->getId(), $message->getCreatedBy()->getId());

        // повторное создание тикета не генерирует ошибку
        $newInstance = $this->manager->createTicket($form, $category, $author);

        $this->assertInstanceOf(TicketEntity::class, $newInstance);
        $this->assertGreaterThan(0, $newInstance->getId());
        $this->assertNotEquals($newInstance->getId(), $result->getId());
        $this->assertNotEquals($newInstance->getNumber(), $result->getNumber());
    }

    /**
     * @depends testCreateTicket
     * @covers TicketManager::createTicketMessage()
     */
    public function testCreateTicketMessage()
    {
        /** @var UserEntity $author */
        $author = $this->fixtures->getReference('ticket-customer-user');
        /** @var UserEntity $manager */
        $manager = $this->fixtures->getReference('ticket-manager');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        // проверка для задания вопроса по тикету
        $this->assertEquals(TicketEntity::STATUS_NEW, $ticket->getCurrentStatus());
        $this->assertInstanceOf(DateTime::class, $ticket->getVoidedAt());

        // после создания сообщения автоматический сброс тикета не возможен
        $form = new TicketMessageType();
        $form->setText('testing ticket text');

        // проверка диспатчера событий по созданию вопросов во событию
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $testCase = $this;
        $eventTriggered = null;
        $dispatcher->addListener(TicketNewMessageEvent::NEW_QUESTION, function($event) use ($form, $testCase, &$eventTriggered, $ticket) {
            $this->assertInstanceOf(TicketNewMessageEvent::class, $event);
            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());

            $testCase->assertInstanceOf(TicketMessageEntity::class, $event->getMessage());
            $testCase->assertGreaterThan(0, $event->getMessage()->getId());
            $testCase->assertEquals($form->getText(), $event->getMessage()->getText());

            $eventTriggered = TicketNewMessageEvent::NEW_QUESTION;
        });

        // проверка диспатчера событий по созданию ответов по событию
        $dispatcher->addListener(TicketNewMessageEvent::NEW_ANSWER, function($event) use (&$form, $testCase, &$eventTriggered, $ticket) {
            $this->assertInstanceOf(TicketNewMessageEvent::class, $event);
            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());

            $testCase->assertInstanceOf(TicketMessageEntity::class, $event->getMessage());
            $testCase->assertGreaterThan(0, $event->getMessage()->getId());
            $testCase->assertEquals($form->getText(), $event->getMessage()->getText());

            $eventTriggered = TicketNewMessageEvent::NEW_ANSWER;
        });

        $result = $this->manager->createTicketMessage($ticket, $form, TicketMessageEntity::TYPE_QUESTION, $author);

        $this->assertEquals(TicketNewMessageEvent::NEW_QUESTION, $eventTriggered);


        // обнулить флаг для дальнейших тестов
        $eventTriggered = null;

        $this->assertInstanceOf(TicketMessageEntity::class, $result);
        $this->assertGreaterThan(0, $result->getId());
        $this->assertInstanceOf(TicketEntity::class, $result->getTicket());
        $this->assertEquals($result->getTicket()->getId(), $ticket->getId());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $result->getCreatedBy());
        $this->assertEquals($author->getId(), $result->getCreatedBy()->getId());
        $this->assertEquals($result->getType(), TicketMessageEntity::TYPE_QUESTION);
        $this->assertInstanceOf(UserEntity::class, $result->getTicket()->getManagedBy());

        // проверка статуса тикета
        $this->assertInstanceOf(DateTime::class, $ticket->getLastQuestionAt());
        $this->assertNull($ticket->getVoidedAt());
        // новая заявка остается новой, пока не получит ответ менеджера
        $this->assertEquals(TicketEntity::STATUS_NEW, $ticket->getCurrentStatus());

        // проверка по ответа по тикету
        $form = new TicketMessageType();
        $form->setText('testing ticket answer');

        $result = $this->manager->createTicketMessage($ticket, $form, TicketMessageEntity::TYPE_ANSWER, $manager);

        $this->assertEquals(TicketNewMessageEvent::NEW_ANSWER, $eventTriggered);

        $this->assertInstanceOf(TicketMessageEntity::class, $result);
        $this->assertGreaterThan(0, $result->getId());
        $this->assertInstanceOf(TicketEntity::class, $result->getTicket());
        $this->assertEquals($result->getTicket()->getId(), $ticket->getId());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $result->getCreatedBy());
        $this->assertEquals($manager->getId(), $result->getCreatedBy()->getId());
        $this->assertEquals($result->getType(), TicketMessageEntity::TYPE_ANSWER);

        $this->assertLastHistoryItem($ticket, TicketEntity::STATUS_ANSWERED, $manager);

        // проверка статуса тикета
        $this->assertInstanceOf(DateTime::class, $ticket->getLastAnswerAt());
        $this->assertInstanceOf(DateTime::class, $ticket->getVoidedAt());
        $this->assertEquals(TicketEntity::STATUS_ANSWERED, $ticket->getCurrentStatus());

        // закрытие тикета сообщением
        $form->setCloseTicket(true);

        $result = $this->manager->createTicketMessage($ticket, $form, TicketMessageEntity::TYPE_ANSWER, $manager);

        $this->assertEquals(TicketNewMessageEvent::NEW_ANSWER, $eventTriggered);
        $this->assertInstanceOf(TicketMessageEntity::class, $result);
        $this->assertGreaterThan(0, $result->getId());
        $this->assertInstanceOf(TicketEntity::class, $result->getTicket());
        $this->assertEquals($result->getTicket()->getId(), $ticket->getId());
        $this->assertEquals(TicketEntity::STATUS_CLOSED, $ticket->getCurrentStatus());
    }

    /**
     * @covers TicketManager::appointTicketToManager()
     */
    public function testAppointTicketToManager()
    {
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager-other');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        // установить обработчик события для проверки
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $testCase = $this;
        $eventDispatched = false;
        $dispatcher->addListener(TicketManagerSetEvent::NAME, function(TicketManagerSetEvent $event) use ($ticket, $managerOther, $testCase, &$eventDispatched) {
            $testCase->assertInstanceOf(UserEntity::class, $event->getManager());
            $testCase->assertEquals($managerOther->getId(), $event->getManager()->getId());

            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());

            $eventDispatched = true;
        });

        $result = $this->manager->appointTicketToManager($ticket, $managerOther);

        $this->assertTrue($eventDispatched);
        $this->assertTrue($result);
        $this->assertInstanceOf(UserEntity::class, $ticket->getManagedBy());
        $this->assertEquals($managerOther->getId(), $ticket->getManagedBy()->getId());
        $this->assertEquals(TicketEntity::STATUS_IN_PROCESS, $ticket->getCurrentStatus());
        $this->assertLastHistoryItem($ticket, TicketEntity::STATUS_IN_PROCESS, $managerOther);

        // повторный запрос должен вернуть false
        $result = $this->manager->appointTicketToManager($ticket, $managerOther);

        $this->assertFalse($result);
    }

    /**
     * @covers TicketManager::closeTicket()
     */
    public function testCloseTicket()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');

        // подписка на событие
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $testCase = $this;
        $eventDispatched = false;
        $dispatcher->addListener(TicketClosedEvent::NAME, function(TicketClosedEvent $event) use ($ticket, $customerUser, $testCase, &$eventDispatched) {
            $testCase->assertInstanceOf(UserEntity::class, $event->getAuthor());
            $testCase->assertEquals($customerUser->getId(), $event->getAuthor()->getId());

            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());
            $eventDispatched = true;
        });

        $result = $this->manager->closeTicket($ticket, $customerUser);

        $this->assertTrue($eventDispatched);
        $this->assertTrue($result);

        $this->assertEquals(TicketEntity::STATUS_CLOSED, $ticket->getCurrentStatus());
        $this->assertLastHistoryItem($ticket, TicketEntity::STATUS_CLOSED, $customerUser);

        // повторно закрыть тикет нельзя
        $eventDispatched = false;
        $result = $this->manager->closeTicket($ticket, $customerUser);

        $this->assertFalse($result);
        $this->assertFalse($eventDispatched);

        // закрытие тикета без участия пользвоателя
        $ticket->setCurrentStatus(TicketEntity::STATUS_ANSWERED);
        $eventDispatched = false;
        $result = $this->manager->closeTicket($ticket, $customerUser);
        $this->assertTrue($result);
        $this->assertTrue($eventDispatched);

        $this->assertEquals(TicketEntity::STATUS_CLOSED, $ticket->getCurrentStatus());
        $this->assertLastHistoryItem($ticket, TicketEntity::STATUS_CLOSED, $customerUser);
    }
}
