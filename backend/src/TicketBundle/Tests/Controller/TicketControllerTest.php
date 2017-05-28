<?php

namespace TicketBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
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

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            TicketTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository();
        $this->ticketRepository = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(TicketEntity::class);
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
                $this->assertStatusCode(400, $client, $k);
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
}
