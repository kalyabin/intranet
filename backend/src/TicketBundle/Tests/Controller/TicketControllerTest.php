<?php

namespace TicketBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\JsonResponseTestTrait;
use TicketBundle\Controller\TicketController;
use TicketBundle\Entity\Repository\TicketRepository;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use TicketBundle\Tests\DataFixtures\ORM\TicketTestFixture;
use UserBundle\Entity\UserEntity;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование контроллера тикетной системы
 *
 * @package TicketBundle\Tests\Controller
 */
class TicketControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;

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
            TicketTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository();
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->ticketRepository = $this->entityManager->getRepository(TicketEntity::class);
    }

    public function assertStatusCode($expectedStatusCode, Client $client)
    {
        parent::assertStatusCode($expectedStatusCode, $client);
        return $this->assertIsValidJsonResponse($client->getResponse());
    }

    /**
     * Проверка контроллера различными пользователями:
     * - неавторизованным пользователем;
     * - авторизованным, но недоступным для правки пользователем;
     * - авторизованным и доступным для правки пользователем
     *
     * @param string $url
     * @param string $method
     * @param string|atring[] $allowedUsers Ключ или ключи в фикстурах для разрешенного пользователя
     * @param string|string[] $deniedUsers КЛюч или ключи для запрещенного пользователя в фикстурах
     * @param array|null $invalidData
     * @param array|null $validData
     *
     * @return array
     */
    public function assertAccessToAction(string $url, string $method, $allowedUsers, $deniedUsers, ?array $invalidData = null, ?array $validData = null): array
    {
        $validData = is_null($validData) ? [] : $validData;

        // пробуем различные варианты неавторизованным пользователем
        $client = $this->createClient();

        if (!is_null($invalidData)) {
            $client->request($method, $url, $invalidData);
            $this->assertStatusCode(401, $client);
        }

        $client->request($method, $url, $validData);
        $this->assertStatusCode(401, $client);

        // пробуем запрещенным пользователем различные варианты
        $deniedUsers = is_array($deniedUsers) ? $deniedUsers : [$deniedUsers];

        foreach ($deniedUsers as $deniedUser) {
            /** @var UserEntity $deniedUser */
            $deniedUser = $this->fixtures->getReference($deniedUser);
            $this->loginAs($deniedUser, 'main');
            $client = static::makeClient();

            if (!is_null($invalidData)) {
                $client->request($method, $url, $invalidData);
                $this->assertStatusCode(403, $client);
            }

            $client->request($method, $url, $validData);

            $this->assertStatusCode(403, $client);
        }

        // пробуем разрешенным пользователем различные варианты
        $allowedUsers = is_array($allowedUsers) ? $allowedUsers : [$allowedUsers];

        $result = [];
        foreach ($allowedUsers as $k => $allowedUser) {
            /** @var UserEntity $allowedUser */
            $allowedUser = $this->fixtures->getReference($allowedUser);

            $this->loginAs($allowedUser, 'main');
            $client = static::makeClient();

            if (!is_null($invalidData)) {
                $client->request($method, $url, $invalidData);
                $this->assertStatusCode(400, $client);
            }

            $client->request($method, $url, $validData);

            $result = $this->assertStatusCode(200, $client);
        }

        return $result;
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
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');

        $url = $this->getUrl('ticket.list', [
            'category' => $category->getId()
        ]);

        $jsonData = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
            'ticket-manager',
            'ticket-customer-user',
        ], [
            'ticket-other-customer-user',
            'ticket-manager-denied',
        ]);

        $this->assertArraySubset([
            'list' => [
                json_decode(json_encode($ticket), true)
            ],
            'pageSize' => 100,
            'pageNum' => 1,
            'totalCount' => 1,
        ], $jsonData);
    }

    /**
     * @covers TicketController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('ticket-category');
        /** @var TicketEntity $ticket */
        $ticket = $this->fixtures->getReference('ticket');

        $url = $this->getUrl('ticket.details', [
            'category' => $category->getId(),
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

        $url = $this->getUrl('ticket.create', [
            'category' => $category->getId(),
        ]);

        $jsonData = $this->assertAccessToAction($url, 'POST', [
            'ticket-customer-user',
        ], [
            'ticket-other-customer-user',
            'ticket-manager',
            'superadmin-user',
            'ticket-manager-denied',
        ], [
            'ticket' => [
                'title' => '',
                'text' => ''
            ]
        ], [
            'ticket' => [
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
            'category' => $ticket->getCategory()->getId(),
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
            'category' => $ticket->getCategory()->getId(),
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
            'category' => $ticket->getCategory()->getId(),
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
