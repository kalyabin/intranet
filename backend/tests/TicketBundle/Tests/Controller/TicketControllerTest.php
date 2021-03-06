<?php

namespace TicketBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AccessToActionTrait;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;
use Tests\JsonResponseTestTrait;
use TicketBundle\Controller\TicketController;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование контроллера тикетной системы
 *
 * @package TicketBundle\Tests\Controller
 */
class TicketControllerTest extends WebTestCase
{
    use AccessToActionTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var TicketRepository
     */
    protected $ticketRepository;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            TicketCategoryTestFixture::class,
            TicketTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->ticketRepository = $this->entityManager->getRepository(TicketEntity::class);
    }

    /**
     * @covers TicketController::categoriesAction()
     */
    public function testCategoriesAction()
    {
        $url = $this->getUrl('ticket.categories');
        $method = 'GET';

        $jsonData = $this->assertAccessToAction($url, $method, [
            'superadmin-user',
            'ticket-manager',
            'ticket-customer-user',
        ], []);

        $this->assertArrayHasKey('list', $jsonData);
        $this->assertNotEmpty($jsonData['list']);
        $this->assertCount(1, $jsonData['list']);

        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');
        foreach ($jsonData['list'] as $item) {
            $this->assertArraySubset(
                json_decode(json_encode($category), true),
                $item
            );
        }

        // для пользователя other-customer-user список недоступен, т.к. нет прав для просмотра IT-аутсорсинга
        $jsonData = $this->assertAccessToAction($url, $method, [
            'ticket-other-customer-user',
        ], []);

        $this->assertArrayHasKey('list', $jsonData);
        $this->assertEmpty($jsonData['list']);

        // для пользователя denied-manager список недоступен, т.к. нет прав для просмотра IT-аутсорсинга
        $jsonData = $this->assertAccessToAction($url, $method, [
            'ticket-manager-denied',
        ], []);

        $this->assertArrayHasKey('list', $jsonData);
        $this->assertEmpty($jsonData['list']);
    }

    /**
     * @covers TicketController::listAction()
     */
    public function testListAction()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.list');

        $jsonData = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
            'ticket-manager',
            'ticket-customer-user',
        ], []);

        $this->assertArraySubset([
            'list' => [
                json_decode(json_encode($ticket), true)
            ],
            'pageSize' => 100,
            'pageNum' => 1,
            'totalCount' => 1,
        ], $jsonData);

        // для указанных пользователей тикетов нет
        foreach (['ticket-other-customer-user', 'ticket-manager-denied'] as $userIdent) {
            $jsonData = $this->assertAccessToAction($url, 'GET', [
                $userIdent
            ], []);

            $this->assertArraySubset([
                'list' => [],
                'pageSize' => 100,
                'pageNum' => 1,
                'totalCount' => 0,
            ], $jsonData);
        }
    }

    /**
     * @covers TicketController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.details', [
            'ticket' => $ticket->getId(),
        ]);

        $jsonData = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
            'ticket-manager',
            'ticket-manager-other',
            'ticket-customer-user',
        ], [
            'ticket-other-customer-user',
            'ticket-manager-denied',
        ]);

        $this->assertArraySubset([
            'ticket' => json_decode(json_encode($ticket), true),
            'messages' => json_decode(json_encode($ticket->getMessage()->getValues()), true),
            'history' => json_decode(json_encode($ticket->getHistory()->getValues()), true),
        ], $jsonData);
    }

    /**
     * @covers TicketController::createTicketAction()
     */
    public function testCreateTicketAction()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $url = $this->getUrl('ticket.create');

        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'ticket-customer-user',
        ], [
            'ticket-other-customer-user',
            'ticket-manager',
            'superadmin-user',
            'ticket-manager-denied',
        ], null, [
            'ticket' => [
                'category' => $category->getId(),
                'title' => 'testing ticket',
                'text' => 'testing message'
            ]
        ]);

        $this->assertArraySubset([
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => null
        ], $jsonData);

        $this->assertArrayHasKey('ticket', $jsonData);
        $this->assertArrayHasKey('id', $jsonData['ticket']);
        $this->assertGreaterThan(0, $jsonData['ticket']);

        // получить тикет для провки массива
        $ticket = $this->ticketRepository->findOneByIdAndCategory($jsonData['ticket']['id'], $category->getId());
        $this->assertArraySubset(json_decode(json_encode($ticket), true), $jsonData['ticket']);
    }

    /**
     * @covers TicketController::messageAction()
     */
    public function testMessageAction()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.message', [
            'ticket' => $ticket->getId()
        ]);

        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'ticket-customer-user',
            'ticket-manager',
            'ticket-manager-other',
            'superadmin-user',
        ], [
            'ticket-manager-denied',
            'ticket-other-customer-user',
        ], [
            'ticket_message' => [
                'text' => ''
            ]
        ], [
            'ticket_message' => [
                'text' => 'testing new message'
            ]
        ]);

        $this->assertArraySubset([
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => null,
        ], $jsonData);

        // проверить данные о тикете
        $this->entityManager->clear(TicketEntity::class);
        $this->entityManager->clear(TicketCategoryEntity::class);

        $ticket = $this->ticketRepository->findOneByIdAndCategory($ticket->getId(), $ticket->getCategory()->getId());
        $lastMessage = $ticket->getMessage()->getValues()[$ticket->getMessage()->count() - 1];

        $this->assertArraySubset([
            'ticket' => json_decode(json_encode($ticket), true),
            'message' => json_decode(json_encode($lastMessage), true)
        ], $jsonData);

        $this->assertArrayHasKey('message', $jsonData);
        $this->assertArrayHasKey('id', $jsonData['message']);
        $this->assertGreaterThan(0, $jsonData['message']['id']);
    }

    /**
     * @covers TicketController::closeAction()
     */
    public function testCloseAction()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.close', [
            'ticket' => $ticket->getId(),
        ]);

        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'ticket-customer-user',
        ], [
            'ticket-manager-denied',
            'ticket-other-customer-user',
        ]);

        $this->assertArrayHasKey('success', $jsonData);
        $this->assertTrue($jsonData['success']);

        // проверить данные о тикете
        $this->entityManager->clear(TicketEntity::class);

        $ticket = $this->ticketRepository->findOneByIdAndCategory($ticket->getId(), $ticket->getCategory()->getId());

        $this->assertArraySubset([
            'ticket' => json_decode(json_encode($ticket), true),
        ], $jsonData);

        // тикет можно закрыть только один раз, но остальные пользователи все таки должны иметь на это право
        $this->assertAccessToAction($url, 'POST', [
            'ticket-manager',
            'ticket-manager-other',
            'superadmin-user',
        ], []);
    }

    /**
     * @covers TicketController::managersAction()
     */
    public function testManagersAction()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $url = $this->getUrl('ticket.managers', [
            'category' => $category->getId()
        ]);

        $jsonData = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
            'ticket-manager',
        ], [
            'ticket-manager-other',
            'ticket-customer-user',
            'ticket-other-customer-user',
            'ticket-manager-denied',
        ]);

        // узнать идентификаторы пользователей группы IT_MANAGEMENT
        $ids = [
            $this->fixtures->getReference('superadmin-user')->getId() => 'superadmin-user',
            $this->fixtures->getReference('ticket-manager')->getId() => 'ticket-manager',
            $this->fixtures->getReference('ticket-manager-other')->getId() => 'ticket-manager-other',
        ];

        $this->assertArrayHasKey('list', $jsonData);
        $this->assertCount(count($ids), $jsonData['list']);
        foreach ($jsonData['list'] as $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey($user['id'], $ids);

            $userEntity = $this->fixtures->getReference($ids[$user['id']]);

            $this->assertArraySubset(json_decode(json_encode($userEntity), true), $user);
        }
    }

    /**
     * @covers TicketController::assignAction()
     */
    public function testAssignAction()
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.assign', [
            'ticket' => $ticket->getId()
        ]);

        // проверить установку менеджерами ответственных самих себя по тикету
        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
            'ticket-manager',
            'ticket-manager-other',
        ], [
            'ticket-customer-user',
            'ticket-other-customer-user',
            'ticket-manager-denied',
        ]);

        $this->assertTrue($jsonData['success']);
        $this->assertArrayHasKey('ticket', $jsonData);
        $this->assertArrayHasKey('id', $jsonData['ticket']);
        $this->assertEquals($ticket->getId(), $jsonData['ticket']['id']);
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertArrayHasKey('id', $jsonData['user']);
        $this->assertGreaterThan(0, $jsonData['user']['id']);

        // и только суперадмин может установить менеджером кого угодно
        /** @var UserEntity $managedBy */
        $managedBy = $this->fixtures->getReference('ticket-manager');
        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
        ], [
            'ticket-customer-user',
            'ticket-other-customer-user',
            'ticket-manager-denied',
            'ticket-manager-other'
        ], null, [
            'managerId' => $managedBy->getId()
        ]);

        // проверить данные о тикете
        $this->entityManager->clear(TicketEntity::class);

        $ticket = $this->ticketRepository->findOneByIdAndCategory($ticket->getId(), $ticket->getCategory()->getId());

        $this->assertArraySubset([
            'success' => true,
            'ticket' => json_decode(json_encode($ticket), true)
        ], $jsonData);
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertArrayHasKey('id', $jsonData['user']);
        $this->assertEquals($managedBy->getId(), $jsonData['user']['id']);
    }
}
