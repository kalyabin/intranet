<?php

namespace TicketBundle\Tests\Utils;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\MailManagerTestTrait;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use TicketBundle\Utils\TicketMailManager;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование мейлера по заявкам
 *
 * @package TicketBundle\Tests\Utils
 */
class TicketMailManagerTest extends WebTestCase
{
    use MailManagerTestTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var TicketMailManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([TicketTestFixture::class])->getReferenceRepository();
        $this->manager = $this->getContainer()->get('ticket.mailer');
    }

    /**
     * @covers TicketMailManager::sendNewTicketToManager()
     */
    public function testSendNewTicketToManager()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-message');
        /** @var UserEntity $manager */
        $manager = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager-other');

        $result = $this->manager->sendNewTicketToManager($ticket, $message);

        $this->assertInternalType('array', $result);
        // должно письмо уйти всем админам
        $this->assertCount(2, $result);
        $this->assertContains($manager->getEmail(), $result);
        $this->assertContains($managerOther->getEmail(), $result);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketMailManager::sendNewTicketToUser()
     */
    public function testSendNewTicketToUser()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-message');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('ticket-customer-user');

        $result = $this->manager->sendNewTicketToUser($ticket, $message);

        $this->assertEquals(1, $result);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketMailManager::sendNewAnswerToUser()
     */
    public function testSendNewAnswerToUser()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('ticket-customer-user');
        /** @var TicketMessageEntity $message */
        $message = $this->fixtures->getReference('ticket-answer');

        $result = $this->manager->sendNewAnswerToUser($ticket, $message);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals($result[0], $user->getEmail());

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketMailManager::sendNewQuestionToManager()
     */
    public function testSendNewQuestionToManager()
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

        $result = $this->manager->sendNewQuestionToManager($ticket, $message);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContains($manager->getEmail(), $result);
        $this->assertContains($managerOther->getEmail(), $result);

        // установить другого менеджера, сообщение должно уйти только ему
        $ticket->setManagedBy($managerOther);

        $result = $this->manager->sendNewQuestionToManager($ticket, $message);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals($managerOther->getEmail(), $result[0]);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($ticket->getTitle());
    }

    /**
     * @covers TicketMailManager::sendSetManagerToUser()
     */
    public function testSendSetManagerToUser()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $managerOther */
        $managerOther = $this->fixtures->getReference('ticket-manager');
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');

        $result = $this->manager->sendSetManagerToUser($ticket, $managerOther);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains($customerUser->getEmail(), $result);

        $this->assertLastMessageContains($ticket->getNumber());
        $this->assertLastMessageContains($managerOther->getName());
    }

    /**
     * @covers TicketMailManager::sendClosedToUser()
     */
    public function testSendClosedToUser()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $customerUser */
        $customerUser = $this->fixtures->getReference('ticket-customer-user');

        $result = $this->manager->sendClosedToUser($ticket);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains($customerUser->getEmail(), $result);

        $this->assertLastMessageContains($ticket->getNumber());
    }
}
