<?php

namespace UserBunde\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\JsonResponseTestTrait;
use Tests\DataFixtures\ORM\UserTestFixture;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use UserBundle\Controller\DashboardController;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserCheckerEntity;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Тестирование контроллера панели пользвоателя
 *
 * @package UserBundle\Tests\Controller
 */
class DashboardControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;

    /**
     * @var ObjectManager
     */
    protected $em;

    protected function setUp()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Тестирование проверки авторизации
     *
     * @covers DashboardController::checkAuthorizationAction()
     */
    public function testCheckAuthorizationActionNotAuth()
    {
        $url = $this->getUrl('user.check_auth');

        $client = $this->createClient();

        // первый запрос должен сказать, что пользователь не авторизован
        $client->request('POST', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'auth' => false,
            'user' => null,
            'roles' => [],
            'isTemporaryPassword' => false,
        ], $jsonData);

        // проверить, что был получен CSRF-токен
        $token = $client->getResponse()->headers->get('X-CSRF-Token');
        $this->assertInternalType('string', $token);
        $this->assertNotEmpty($token);
    }

    /**
     * Тестирование проверки авторизации
     *
     * @covers DashboardController::checkAuthorizationAction()
     */
    public function testCheckAuthorizationActionAuth()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('superadmin-user');

        $url = $this->getUrl('user.check_auth');

        // автроризуем пользователя
        $this->loginAs($user, 'main');

        $client = static::makeClient();

        // второй запрос должен сказать, что пользователь авторизован
        $client->request('POST', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'auth' => true,
            'user' => json_decode(json_encode($user), true),
            'isTemporaryPassword' => false
        ], $jsonData);

        $this->assertInternalType('array', $jsonData['roles']);
        $this->assertGreaterThan(1, count($jsonData['roles']));

        // проверить, что был получен CSRF-токен
        $token = $client->getResponse()->headers->get('X-CSRF-Token');
        $this->assertInternalType('string', $token);
        $this->assertNotEmpty($token);
    }

    /**
     * Тестирование обновления e-mail
     *
     * @covers DashboardController::changeEmailAction()
     */
    public function testChangeEmailAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $url = $this->getUrl('user.change_email');

        // неавторизованные пользователи не могут смотреть данный экшн
        $client = $this->createClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // авторизуемся
        $this->loginAs($user, 'main');
        $client = static::makeClient();

        // отправляем пустой запрос
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'success' => false,
            'submitted' => false,
            'valid' => false,
            'validationErrors' => []
        ], $jsonData);

        // отправляем невалидный запрос
        $client->request('POST', $url, [
            'change_email' => [
                'newEmail' => 'wrong email',
            ],
        ]);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'submitted' => true,
            'valid' => false,
        ], $jsonData);
        $this->assertNotEmpty($jsonData['validationErrors']);
        $this->assertArrayHasKey('change_email[newEmail]', $jsonData['validationErrors']);

        // отправляем валидный запрос
        $client->request('POST', $url, [
            'change_email' => [
                'newEmail' => 'newemail@test.ru',
            ],
        ]);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => []
        ], $jsonData);
    }

    /**
     * Субмит формы изменения пароля
     *
     * @covers DashboardController::changePasswordAction()
     */
    public function testChangePasswordAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $url = $this->getUrl('user.change_password');

        // неавторизованные пользователи не могут смотреть данный экшн
        $client = $this->createClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // авторизуемся
        $this->loginAs($user, 'main');
        $client = static::makeClient();

        // отправляем пустой запрос
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'success' => false,
            'submitted' => false,
            'valid' => false,
            'validationErrors' => []
        ], $jsonData);

        // отправляем невалидный запрос
        $client->request('POST', $url, [
            'change_password' => [
                'password' => [
                    'first' => '',
                    'second' => '',
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

        // отправляем валидный запрос
        $this->loginAs($user, 'main');
        $client = static::makeClient();
        $client->request('POST', $url, [
            'change_password' => [
                'password' => [
                    'first' => 'new testing password',
                    'second' => 'new testing password',
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

        // проверяем, что пароль действительно сменился
        $oldPassword = $user->getPassword();
        $this->em->clear();
        $user = $this->em->getRepository(UserEntity::class)->findOneById($user->getId());
        $this->assertNotEquals($user->getPassword(), $oldPassword);

        // после изменения пароля сессия не должна сбрасываться
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);
    }

    /**
     * Субмит формы изменения профиля
     *
     * @covers DashboardController::profileUpdateAction()
     */
    public function testProfileUpdateAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $url = $this->getUrl('user.profile_update');

        // неавторизованные пользователи не могут смотреть данный экшн
        $client = $this->createClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // авторизуемся
        $this->loginAs($user, 'main');
        $client = static::makeClient();

        // отправляем пустой запрос
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'success' => false,
            'submitted' => false,
            'valid' => false,
            'validationErrors' => []
        ], $jsonData);

        // отправляем невалидный запрос
        $client->request('POST', $url, [
            'profile' => [
                'name' => '',
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
        $this->assertArrayHasKey('profile[name]', $jsonData['validationErrors']);

        // отправляем валидный запрос
        $client->request('POST', $url, [
            'profile' => [
                'name' => 'New testing name',
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

        // проверяем, что имя действительно сменилось
        $this->em->clear();
        $user = $this->em->getRepository(UserEntity::class)->findOneById($user->getId());
        $this->assertEquals('New testing name', $user->getName());
    }

    /**
     * Тестирование подтверждения изменения e-mail
     *
     * @covers DashboardController::confirmChangeEmailAction()
     */
    public function testConfirmChangeEmailAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');

        $newEmail = 'newemail@test.ru';

        // создать код подтверждения
        $checker = new UserCheckerEntity();

        $checker
            ->setUser($user)
            ->setType(UserCheckerEntity::TYPE_CHANGE_EMAIL)
            ->setJsonData(['newEmail' => $newEmail])
            ->generateCode();

        $user->addChecker($checker);

        $this->em->persist($checker);
        $this->em->persist($user);

        $this->em->flush();

        $client = $this->createClient();

        // сначала пробуем все ошибки
        $client->request('GET', $this->getUrl('user.change_email_confirmation', [
            'checkerId' => 0,
            'code' => 'wrongcode',
        ]));
        $this->assertStatusCode(404, $client);
        $client->request('GET', $this->getUrl('user.change_email_confirmation', [
            'checkerId' => $checker->getId(),
            'code' => 'wrongcode',
        ]));
        $this->assertStatusCode(404, $client);

        // далее подтверждаем
        $client->request('GET', $this->getUrl('user.change_email_confirmation', [
            'checkerId' => $checker->getId(),
            'code' => $checker->getCode(),
        ]));
        $this->assertStatusCode(302, $client);
        $this->assertEquals('/#/email-updated/' . $newEmail, $client->getResponse()->headers->get('location'));

        // проверяем, что e-mail действительно сменился
        $this->em->clear();

        /** @var UserEntity $user */
        $user = $this->em->getRepository(UserEntity::class)->findOneById($user->getId());

        $this->assertEquals($newEmail, $user->getEmail());
    }

    /**
     * Тестирование логаута пользователя
     *
     * @covers DashboardController::logoutAction()
     */
    public function testLogoutAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');
        $this->loginAs($user, 'main');
        $client = parent::makeClient();
        $url = $this->getUrl('user.logout');
        $client->request('POST', $url);

        // редирект на генерацию нового токена
        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }

    /**
     * Тестирование обновления токена
     *
     * @covers DashboardController::generateTokenAction()
     */
    public function testGenerateTokenAction()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([ServiceTestFixture::class, CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository()->getReference('active-user');
        $this->loginAs($user, 'main');
        $client = parent::makeClient();

        $url = $this->getUrl('user.token');

        $client->request('HEAD', $url);

        $this->assertStatusCode(200, $client);
        $firstToken = $client->getResponse()->headers->get('X-CSRF-Token');
        $this->assertInternalType('string', $firstToken);
        $this->assertNotEmpty($firstToken);

        // проверить, что токен обновляется
        $client->request('HEAD', $url);

        $this->assertStatusCode(200, $client);
        $secondToken = $client->getResponse()->headers->get('X-CSRF-Token');
        $this->assertInternalType('string', $secondToken);
        $this->assertNotEmpty($secondToken);

        $this->assertNotEquals($firstToken, $secondToken);
    }
}
