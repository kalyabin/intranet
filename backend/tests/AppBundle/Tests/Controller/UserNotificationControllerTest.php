<?php

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\UserNotificationController;
use AppBundle\Entity\UserNotificationEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\TicketCategoryTestFixture;
use Tests\DataFixtures\ORM\TicketTestFixture;
use Tests\DataFixtures\ORM\UserNotificationTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use Tests\JsonResponseTestTrait;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование контроллера нотификаций
 *
 * @package AppBundle\Tests\Controller
 */
class UserNotificationControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserNotificationTestFixture::class,
            CustomerTestFixture::class,
            TicketCategoryTestFixture::class,
            UserTestFixture::class,
            TicketTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers UserNotificationController::unreadListAction()
     */
    public function testUnreadListAction()
    {
        $url = $this->getUrl('notifications');

        /** @var UserEntity $unexpectedUser */
        $unexpectedUser = $this->fixtures->getReference('superadmin-user');
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('active-user');
        /** @var UserNotificationEntity $event1 */
        $event1 = $this->fixtures->getReference('active-user-notification');
        /** @var UserNotificationEntity $event2 */
        $event2 = $this->fixtures->getReference('active-user-notification-second');

        // тестирование для неавторизованного пользователя
        $client = $this->createClient();

        $client->request('GET', $url);
        $this->assertStatusCode(401, $client);

        // тестирование для авторизованного пользователя, которому не принадлежат уведомления
        $this->loginAs($unexpectedUser, 'main');
        $client = static::makeClient();
        $client->request('GET', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'list' => [],
        ], $jsonData);

        // тестирование для авторизованного пользователя, которому принадлежат уведомления
        $this->loginAs($expectedUser, 'main');
        $client = static::makeClient();
        $client->request('GET', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('list', $jsonData);
        $this->assertInternalType('array', $jsonData['list']);
        $this->assertCount(2, $jsonData['list']);
        foreach ($jsonData['list'] as $item) {
            $this->assertInternalType('array', $item);
            $this->assertArrayHasKey('id', $item);
            if ($item['id'] == $event1->getId()) {
                $this->assertArraySubset(json_decode(json_encode($event1), true), $item);
            } else {
                $this->assertArraySubset(json_decode(json_encode($event2), true), $item);
            }
        }
    }

    /**
     * @covers UserNotificationController::readAllAction()
     */
    public function testReadAllAction()
    {
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('active-user');
        /** @var UserEntity $unexpectedUser */
        $unexpectedUser = $this->fixtures->getReference('superadmin-user');

        $url = $this->getUrl('notifications.read');

        // тестирование для неавторизованного пользователя
        $client = $this->createClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // тестирование для авторизованного пользователя, у которого нет записей
        $this->loginAs($unexpectedUser, 'main');
        $client = static::makeClient();
        // только POST-запрос
        $client->request('GET', $url);
        $this->assertStatusCode(405, $client);
        $client->request('POST', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'updateMessages' => 0,
        ], $jsonData);

        // тестирование для авторизованного пользователя у которого есть записи
        $this->loginAs($expectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'updateMessages' => 2, // в фикстурах ровно столько непрочитанных уведомлений
        ], $jsonData);

        // повторный запрос должен вернуть 0
        $client->request('POST', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'updateMessages' => 0
        ], $jsonData);
    }
}
