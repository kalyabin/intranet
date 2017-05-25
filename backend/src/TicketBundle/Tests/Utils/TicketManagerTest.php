<?php

namespace TicketBundle\Tests\Utils;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Event\TicketNewEvent;
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
        $dispatcher->addListener(TicketNewEvent::NEW_TICKET, function(TicketNewEvent $event) use ($form, $testCase, &$eventTriggered) {
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
        $dispatcher->addListener(TicketNewEvent::NEW_QUESTION, function(TicketNewEvent $event) use ($form, $testCase, &$eventTriggered, $ticket) {
            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());

            $testCase->assertInstanceOf(TicketMessageEntity::class, $event->getMessage());
            $testCase->assertGreaterThan(0, $event->getMessage()->getId());
            $testCase->assertEquals($form->getText(), $event->getMessage()->getText());

            $eventTriggered = TicketNewEvent::NEW_QUESTION;
        });

        // проверка диспатчера событий по созданию ответов по событию
        $dispatcher->addListener(TicketNewEvent::NEW_ANSWER, function(TicketNewEvent $event) use (&$form, $testCase, &$eventTriggered, $ticket) {
            $testCase->assertInstanceOf(TicketEntity::class, $event->getTicket());
            $testCase->assertEquals($ticket->getId(), $event->getTicket()->getId());

            $testCase->assertInstanceOf(TicketMessageEntity::class, $event->getMessage());
            $testCase->assertGreaterThan(0, $event->getMessage()->getId());
            $testCase->assertEquals($form->getText(), $event->getMessage()->getText());

            $eventTriggered = TicketNewEvent::NEW_ANSWER;
        });

        $result = $this->manager->createTicketMessage($ticket, $form, TicketMessageEntity::TYPE_QUESTION, $author);

        $this->assertEquals(TicketNewEvent::NEW_QUESTION, $eventTriggered);

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
        $this->assertEquals(TicketEntity::STATUS_WAIT, $ticket->getCurrentStatus());

        // проверка по ответа по тикету
        $form = new TicketMessageType();
        $form->setText('testing ticket answer');

        $result = $this->manager->createTicketMessage($ticket, $form, TicketMessageEntity::TYPE_ANSWER, $manager);

        $this->assertEquals(TicketNewEvent::NEW_ANSWER, $eventTriggered);

        $this->assertInstanceOf(TicketMessageEntity::class, $result);
        $this->assertGreaterThan(0, $result->getId());
        $this->assertInstanceOf(TicketEntity::class, $result->getTicket());
        $this->assertEquals($result->getTicket()->getId(), $ticket->getId());
        $this->assertInstanceOf(DateTime::class, $result->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $result->getCreatedBy());
        $this->assertEquals($manager->getId(), $result->getCreatedBy()->getId());
        $this->assertEquals($result->getType(), TicketMessageEntity::TYPE_ANSWER);

        // проверка статуса тикета
        $this->assertInstanceOf(DateTime::class, $ticket->getLastAnswerAt());
        $this->assertInstanceOf(DateTime::class, $ticket->getVoidedAt());
        $this->assertEquals(TicketEntity::STATUS_ANSWERED, $ticket->getCurrentStatus());
    }
}
