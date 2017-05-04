<?php

namespace UserBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\JsonResponseTestTrait;
use UserBundle\Controller\ManagerController;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserEntity;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование ManagerController
 *
 * @package UserBundle\Tests\Controller
 */
class ManagerControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->fixtures = $this->loadFixtures([UserTestFixture::class])->getReferenceRepository();
    }

    protected function assertNonAuthenticatedUsers($method, $url, $postData = [])
    {
        $nonAdminUser = $this->fixtures->getReference('active-user');

        $client = $this->createClient();

        // неавторизованный пользователь должен видеть 401 ошибку
        $client->request($method, $url, $postData);
        $this->assertStatusCode(401, $client);

        // авторизованный пользователь но не админ должен получить 403-ю ошибку
        $this->loginAs($nonAdminUser, 'main');

        $client = static::makeClient();
        $client->request($method, $url, $postData);
        $this->assertStatusCode(403, $client);
    }

    /**
     * Тестирование создания пользователя
     *
     * @covers ManagerController::createAction()
     */
    public function testCreateAction()
    {
        $url = $this->getUrl('user.manager.create');

        /** @var UserEntity $superAdminUser */
        $superAdminUser = $this->fixtures->getReference('superadmin-user');

        $invalidPostData = [
            'user' => [],
        ];

        $validPostData = [
            'user' => [
                'name' => 'testing',
                'email' => 'testcreateaction@test.ru',
                // пароль как правило будет генерироваться автоматически
                'isTemporaryPassword' => true,
                'userType' => UserEntity::TYPE_CUSTOMER,
                'role' => [
                    [
                        'code' => 'CUSTOMER_ADMIN'
                    ]
                ]
            ]
        ];

        $this->assertNonAuthenticatedUsers('POST', $url, $validPostData);

        // от супер-админа попробовать пробросить ошибочный запрос
        $this->loginAs($superAdminUser, 'main');

        $client = static::makeClient();
        $client->request('POST', $url, $invalidPostData);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertNull($jsonData['user']);

        // от супер-админа пробросить нормальный запрос
        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertInternalType('array', $jsonData['user']);
        $this->assertArrayHasKey('id', $jsonData['user']);
        $this->assertGreaterThan(0, $jsonData['user']['id']);
        $this->assertArrayHasKey('email', $jsonData['user']);
        $this->assertEquals($validPostData['user']['email'], $jsonData['user']['email']);
        $this->assertArrayHasKey('success', $jsonData);
        $this->assertTrue($jsonData['success']);
    }

    /**
     * Тестирование редактирования пользователя
     *
     * @covers ManagerController::updateAction()
     */
    public function testUpdateAction()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('inactive-user');
        /** @var UserEntity $superAdminUser */
        $superAdminUser = $this->fixtures->getReference('superadmin-user');

        $invalidPostData = [
            'user' => [],
        ];

        $validPostData = [
            'user' => [
                'name' => 'testing',
                'email' => 'testupdateaction@test.ru',
                'password' => 'userpassword',
                'userType' => UserEntity::TYPE_CUSTOMER,
                'status' => UserEntity::STATUS_ACTIVE,
                'role' => [
                    [
                        'code' => 'CUSTOMER_ADMIN'
                    ]
                ]
            ]
        ];

        $url = $this->getUrl('user.manager.update', [
            'id' => $user->getId()
        ]);

        $this->assertNonAuthenticatedUsers('POST', $url, $validPostData);

        // от супер-админа попробовать пробросить ошибочный запрос
        $this->loginAs($superAdminUser, 'main');

        $client = static::makeClient();
        $client->request('POST', $url, $invalidPostData);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertInternalType('array', $jsonData['user']);

        // от супер-админа пробросить нормальный запрос
        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertInternalType('array', $jsonData['user']);
        $this->assertArrayHasKey('id', $jsonData['user']);
        $this->assertGreaterThan(0, $jsonData['user']['id']);
        $this->assertArrayHasKey('email', $jsonData['user']);
        $this->assertEquals($validPostData['user']['email'], $jsonData['user']['email']);
        $this->assertArrayHasKey('success', $jsonData);
        $this->assertTrue($jsonData['success']);

        // проверить, что пользователь был успешно активирован
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(UserEntity::class);
        $expectedUser = $repository->findOneById($user->getId());
        $this->assertInstanceOf(UserEntity::class, $expectedUser);
        $this->assertTrue($expectedUser->isActive());
    }

    /**
     * @covers ManagerController::listAction()
     */
    public function testListAction()
    {
        $pageNum = 2;
        $pageSize = 1;

        $url = $this->getUrl('user.manager.list', [
            'pageNum' => $pageNum,
            'pageSize' => $pageSize
        ]);

        $allUsers = $this->fixtures->getReferences();
        $expectedCount = count($allUsers);

        /** @var UserEntity $superAdminUser */
        $superAdminUser = $this->fixtures->getReference('superadmin-user');

        $this->assertNonAuthenticatedUsers('GET', $url);

        // от супер-админа пробросить нормальный запрос
        $this->loginAs($superAdminUser, 'main');

        $client = static::makeClient();
        $client->request('GET', $url);
        $this->assertStatusCode(200, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('list', $jsonData);
        $this->assertArrayHasKey('pageSize', $jsonData);
        $this->assertArrayHasKey('pageNum', $jsonData);
        $this->assertArrayHasKey('totalCount', $jsonData);
        $this->assertInternalType('array', $jsonData['list']);
        $this->assertInternalType('integer', $jsonData['pageSize']);
        $this->assertInternalType('integer', $jsonData['pageNum']);
        $this->assertInternalType('integer', $jsonData['totalCount']);
        $this->assertCount($pageSize, $jsonData['list']);
        $this->assertEquals($pageSize, $jsonData['pageSize']);
        $this->assertEquals($pageNum, $jsonData['pageNum']);
        $this->assertEquals($expectedCount, $jsonData['totalCount']);

        foreach ($jsonData['list'] as $item) {
            $this->assertInternalType('array', $item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('email', $item);
        }
    }

    /**
     * @covers ManagerController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');
        /** @var UserEntity $superAdminUser */
        $superAdminUser = $this->fixtures->getReference('superadmin-user');

        $url = $this->getUrl('user.manager.details', [
            'id' => $user->getId()
        ]);

        $this->assertNonAuthenticatedUsers('GET', $url);

        // от супер-админа
        $this->loginAs($superAdminUser, 'main');

        $client = static::makeClient();
        $client->request('GET', $url);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertArrayHasKey('roles', $jsonData);
        $this->assertArrayHasKey('status', $jsonData);
        $this->assertInternalType('array', $jsonData['user']);
        $this->assertInternalType('array', $jsonData['roles']);
        $this->assertInternalType('integer', $jsonData['status']);
        $this->assertArrayHasKey('id', $jsonData['user']);
        $this->assertEquals($user->getId(), $jsonData['user']['id']);
        $this->assertEquals($user->getStatus(), $jsonData['status']);
    }
}
