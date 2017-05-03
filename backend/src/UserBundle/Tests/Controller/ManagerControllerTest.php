<?php

namespace UserBundle\Tests\Controller;


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

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Тестирование создания пользователя
     *
     * @covers ManagerController::createAction()
     */
    public function testCreateAction()
    {
        $fixtures = $this->loadFixtures([UserTestFixture::class])->getReferenceRepository();

        /** @var UserEntity $nonAdminUser */
        $nonAdminUser = $fixtures->getReference('active-user');
        /** @var UserEntity $superAdminUser */
        $superAdminUser = $fixtures->getReference('superadmin-user');

        $client = $this->createClient();

        $invalidPostData = [
            'user' => [],
        ];

        $validPostData = [
            'user' => [
                'name' => 'testing',
                'email' => 'testcreateaction@test.ru',
                'password' => 'userpassword',
                'userType' => UserEntity::TYPE_CUSTOMER,
                'role' => [
                    [
                        'code' => 'CUSTOMER_ADMIN'
                    ]
                ]
            ]
        ];

        // неавторизованный пользователь должен видеть 401 ошибку
        $client->request('POST', $this->getUrl('user.manager.create'), $validPostData);
        $this->assertStatusCode(401, $client);

        // авторизованный пользователь но не админ должен получить 403-ю ошибку
        $this->loginAs($nonAdminUser, 'main');

        $client = static::makeClient();
        $client->request('POST', $this->getUrl('user.manager.create'), $validPostData);
        $this->assertStatusCode(403, $client);

        // от супер-админа попробовать пробросить ошибочный запрос
        $this->loginAs($superAdminUser, 'main');

        $client = static::makeClient();
        $client->request('POST', $this->getUrl('user.manager.create'), $invalidPostData);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArrayHasKey('user', $jsonData);
        $this->assertNull($jsonData['user']);

        // от супер-админа пробросить нормальный запрос
        $client->request('POST', $this->getUrl('user.manager.create'), $validPostData);
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
        $fixtures = $this->loadFixtures([UserTestFixture::class])->getReferenceRepository();

        /** @var UserEntity $user */
        $user = $fixtures->getReference('inactive-user');
        /** @var UserEntity $nonAdminUser */
        $nonAdminUser = $fixtures->getReference('active-user');
        /** @var UserEntity $superAdminUser */
        $superAdminUser = $fixtures->getReference('superadmin-user');

        $client = $this->createClient();

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

        // неавторизованный пользователь должен видеть 401 ошибку
        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(401, $client);

        // авторизованный пользователь но не админ должен получить 403-ю ошибку
        $this->loginAs($nonAdminUser, 'main');

        $client = static::makeClient();
        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(403, $client);

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
}
