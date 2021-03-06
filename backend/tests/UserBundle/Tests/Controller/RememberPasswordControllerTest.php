<?php

namespace UserBunde\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\JsonResponseTestTrait;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Controller\RememberPasswordController;
use UserBundle\Entity\UserCheckerEntity;
use UserBundle\Entity\UserEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\DataFixtures\ORM\CustomerTestFixture;

/**
 * Тестирование класса RememberPasswordController
 *
 * @package UserBundle\Tests\Controller
 */
class RememberPasswordControllerTest extends WebTestCase
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
     * @covers RememberPasswordController::changePasswordAction()
     * @covers RememberPasswordController::confirmRememberCode()
     */
    public function testChangePasswordAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $currentPassword = $user->getPassword();
        $newPassword = 'newtestpassword';

        // создать чекер
        $checker = new UserCheckerEntity();
        $checker
            ->setType(UserCheckerEntity::TYPE_REMEMBER_PASSWORD)
            ->setUser($user)
            ->generateCode();

        $user->addChecker($checker);

        $this->em->persist($user);
        $this->em->flush();

        $client = $this->createClient();

        $url = $this->getUrl('remember_password.change_password', [
            'checkerId' => $checker->getId(),
            'code' => $checker->getCode(),
        ]);

        // стучимся с неправильными данными
        $client->request('POST', $url);

        $this->assertStatusCode(400, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'submitted' => false,
            'valid' => false,
            'validationErrors' => []
        ], $jsonData);

        $client->request('POST', $url, [
            'change_password' => [
                'password' => [
                    'first' => $newPassword,
                ]
            ]
        ]);

        $this->assertStatusCode(400, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'submitted' => true,
            'valid' => false,
        ], $jsonData);
        $this->assertNotEmpty($jsonData['validationErrors']);
        $this->assertArrayHasKey('change_password[password][first]', $jsonData['validationErrors']);

        $client->request('POST', $url, [
            'change_password' => [
                'password' => [
                    'second' => $newPassword,
                ]
            ]
        ]);

        $this->assertStatusCode(400, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'submitted' => true,
            'valid' => false,
        ], $jsonData);
        $this->assertNotEmpty($jsonData['validationErrors']);
        $this->assertArrayHasKey('change_password[password][first]', $jsonData['validationErrors']);

        // стучимся с нормальными данными
        $client->request('POST', $url, [
            'change_password' => [
                'password' => [
                    'first' => $newPassword,
                    'second' => $newPassword,
                ]
            ]
        ]);

        $this->assertStatusCode(200, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => []
        ], $jsonData);

        // проверить, что пароль действительно изменился
        $this->em->clear();

        /** @var \UserBundle\Entity\Repository\UserRepository $repository */
        $repository = $this->em->getRepository(UserEntity::class);

        $user = $repository->findOneById($user->getId());
        $this->assertInstanceOf(UserEntity::class, $user);

        $this->assertNotEquals($user->getPassword(), $currentPassword);
    }

    /**
     * @covers RememberPasswordController::changePasswordFormAction()
     * @covers RememberPasswordController::confirmRememberCode()
     */
    public function testChangePasswordFormAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        // создать чекер
        $checker = new UserCheckerEntity();
        $checker
            ->setType(UserCheckerEntity::TYPE_REMEMBER_PASSWORD)
            ->setUser($user)
            ->generateCode();

        $user->addChecker($checker);

        $this->em->persist($user);
        $this->em->flush();

        $client = $this->createClient();

        // стучимся с несуществующим чекером
        $url = $this->getUrl('remember_password.change_password_form', [
            'checkerId' => 0,
            'code' => 'wrongcheckercode',
        ]);

        $client->request('GET', $url);
        $this->assertStatusCode(404, $client);

        // стучимся с неправильным кодом
        $url = $this->getUrl('remember_password.change_password_form', [
            'checkerId' => $checker->getId(),
            'code' => 'wrongcheckercode',
        ]);

        $client->request('GET', $url);
        $this->assertStatusCode(404, $client);

        // правильный код
        $url = $this->getUrl('remember_password.change_password_form', [
            'checkerId' => $checker->getId(),
            'code' => $checker->getCode(),
        ]);

        $client->request('GET', $url);
        $this->assertStatusCode(302, $client);

        $this->assertEquals('/#/change-password/' . $checker->getId() . '/' . $checker->getCode(), $client->getResponse()->headers->get('location'));
    }

    /**
     * @covers RememberPasswordController::rememberAction()
     */
    public function testRememberAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $this->assertEmpty($user->getCheckerByType(UserCheckerEntity::TYPE_REMEMBER_PASSWORD));

        $client = $this->createClient();

        $url = $this->getUrl('remember_password.remember');

        // GET-ом сюда стучаться нельзя
        $client->request('GET', $url);
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        // пустой POST
        $client->request('POST', $url);
        $response = $client->getResponse();
        $jsonData = $this->assertIsValidJsonResponse($response);

        $this->assertStatusCode(400, $client);
        $this->assertArraySubset([
            'email' => null,
            'success' => false,
            'submitted' => false,
            'valid' => false,
            'validationErrors' => []
        ], $jsonData);

        // POST от несуществующего пользователя
        $client->request('POST', $url, [
            'remember_password' => [
                'email' => 'non-existent@email.ru',
            ]
        ]);

        $response = $client->getResponse();
        $jsonData = $this->assertIsValidJsonResponse($response);

        $this->assertStatusCode(400, $client);
        $this->assertArraySubset([
            'email' => null,
            'success' => false,
            'submitted' => true,
            'valid' => false,
        ], $jsonData);
        $this->assertNotEmpty($jsonData['validationErrors']);
        $this->assertArrayHasKey('remember_password[email]', $jsonData['validationErrors']);

        // нормальный POST
        $client->request('POST', $url, [
            'remember_password' => [
                'email' => $user->getEmail(),
            ]
        ]);

        $response = $client->getResponse();
        $jsonData = $this->assertIsValidJsonResponse($response);

        $this->assertStatusCode(200, $client);
        $this->assertArraySubset([
            'email' => $user->getEmail(),
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => []
        ], $jsonData);

        // проверить, что создался чекер
        $this->em->clear();

        /** @var \UserBundle\Entity\Repository\UserRepository $repository */
        $repository = $this->em->getRepository(UserEntity::class);

        $user = $repository->findOneById($user->getId());

        $this->assertInstanceOf(UserEntity::class, $user);
        $this->assertInstanceOf(UserCheckerEntity::class, $user->getCheckerByType(UserCheckerEntity::TYPE_REMEMBER_PASSWORD));
    }
}
