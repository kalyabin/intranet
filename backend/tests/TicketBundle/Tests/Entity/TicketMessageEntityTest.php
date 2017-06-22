<?php

namespace TicketBundle\Tests\Entity;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Entity\TicketMessageEntity;
use Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование сообщений по тикету
 *
 * @package TicketBundle\Tests\Entity
 */
class TicketMessageEntityTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class,
            TicketCategoryTestFixture::class,
            TicketTestFixture::class
        ])->getReferenceRepository();
    }

    /**
     * @covers TicketMessageEntity::getText()
     * @covers TicketMessageEntity::getCreatedAt()
     * @covers TicketMessageEntity::getCreatedBy()
     * @covers TicketMessageEntity::getTicket()
     * @covers TicketMessageEntity::getType()
     *
     * @covers TicketMessageEntity::setText()
     * @covers TicketMessageEntity::setCreatedAt()
     * @covers TicketMessageEntity::setCreatedBy()
     * @covers TicketMessageEntity::setTicket()
     * @covers TicketMessageEntity::setType()
     */
    public function testMe()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');
        /** @var UserEntity $userManager */
        $userManager = $this->fixtures->getReference('ticket-manager');

        $entity = new TicketMessageEntity();

        $this->assertNull($entity->getText());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getTicket());
        $this->assertNull($entity->getType());

        $entity
            ->setType(TicketMessageEntity::TYPE_ANSWER)
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($userManager)
            ->setTicket($ticket)
            ->setText('testing text');

        $this->assertEquals(TicketMessageEntity::TYPE_ANSWER, $entity->getType());
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());
        $this->assertInstanceOf(TicketEntity::class, $entity->getTicket());
        $this->assertEquals('testing text', $entity->getText());

        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $em->persist($entity);

        $em->flush();
    }
}
