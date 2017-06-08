<?php

namespace UserBundle\Tests\Controller;


use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Tests\JsonResponseTestTrait;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Controller\LoginController;
use UserBundle\Entity\UserEntity;
use Tests\DataFixtures\ORM\CustomerTestFixture;

/**
 * Тестирование класса LoginController
 *
 * @package UserBundle\Tests\Controller
 */
class LoginControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;

    /**
     * Тестирование авторизации по логин-паролю
     */
    public function testSimpleLoginAction()
    {
        // загрузить пользователей
        $fixtures = $this->loadFixtures([CustomerTestFixture::class, UserTestFixture::class])->getReferenceRepository();

        /** @var UserEntity $activeUser */
        $activeUser = $fixtures->getReference('active-user');
        /** @var UserEntity $inactiveUser */
        $inactiveUser = $fixtures->getReference('inactive-user');
        /** @var UserEntity $lockedUser */
        $lockedUser = $fixtures->getReference('locked-user');

        // пароль для всех
        $password = 'testpassword';

        $url = $this->getUrl('login.simple_check');

        $client = $this->createClient();
        $session = $client->getContainer()->get('session');
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        // отправить пустой POST
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'loggedIn' => false,
            'isLocked' => false,
            'isNeedActivation' => false,
            'userId' => null,
            'errorMessage' => 'Неверный логин или пароль'
        ], $jsonData);

        // отправить неверный логин или пароль
        $client->request('POST', $url, [
            '_username' => 'non-existent@email.ru',
            '_password' => 'wrongpassword',
        ]);
        $this->assertStatusCode(401, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'loggedIn' => false,
            'isLocked' => false,
            'isNeedActivation' => false,
            'userId' => null,
            'errorMessage' => 'Неверный логин или пароль'
        ], $jsonData);

        // авторизоваться под неактивным пользователем
        $client->request('POST', $url, [
            '_username' => $inactiveUser->getUsername(),
            '_password' => $password,
        ]);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'loggedIn' => false,
            'isLocked' => false,
            'isNeedActivation' => true,
            'userId' => $inactiveUser->getId(),
            'errorMessage' => 'Требуется активация'
        ], $jsonData);

        // авторизоваться под заблокированным пользователем
        $client->request('POST', $url, [
            '_username' => $lockedUser->getUsername(),
            '_password' => $password,
        ]);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'loggedIn' => false,
            'isLocked' => true,
            'isNeedActivation' => false,
            'userId' => $lockedUser->getId(),
            'errorMessage' => 'Ваш аккаунт заблокирован'
        ], $jsonData);

        // авторизоваться под активным пользователем
        $client->request('POST', $url, [
            '_username' => $activeUser->getUsername(),
            '_password' => $password,
        ]);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertArraySubset([
            'loggedIn' => true,
            'isLocked' => false,
            'isNeedActivation' => false,
            'userId' => $activeUser->getId(),
            'errorMessage' => ''
        ], $jsonData);
    }
}
