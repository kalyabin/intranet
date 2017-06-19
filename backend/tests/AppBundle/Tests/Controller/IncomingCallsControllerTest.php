<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Controller\IncomingCallsController;
use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\User;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use Tests\JsonResponseTestTrait;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование контроллера по получению и переотправке входящих звонков
 *
 * @package AppBundle\Tests\Controller
 */
class IncomingCallsControllerTest extends WebTestCase
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
            CustomerTestFixture::class,
            UserTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * Тестирование получения входящих звонков от АТС секретарём
     *
     * @covers IncomingCallsController::receiveIncomingCall()
     */
    public function testReceiveIncomingCalls()
    {
        // пароль для АТСки
        $atsPassword = $this->getContainer()->getParameter('ats_password');

        // пользователи, которым должны упасть уведомления
        $needUsers = [];
        foreach ($this->fixtures->getReferences() as $reference) {
            /** @var UserEntity $reference */
            if ($reference instanceof UserEntity && (in_array('ROLE_SUPERADMIN', $reference->getRoles()) || in_array('ROLE_INCOMING_CALLS_MANAGEMENT', $reference->getRoles()))) {
                $needUsers[] = $reference->getEmail();
            }
        }
        $this->assertNotEmpty($needUsers);

        $callerId = '74951111111';

        $url = $this->getUrl('incoming-calls.receive');

        // пробуем неавторизованным пользователем
        $client = $this->createClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // авторзуемся с неправильным паролем
        $user = new User('ats', 'wrong password');
        $this->loginAs($user, 'ats_area');
        $client = static::makeClient();
        $client->request('POST', $url);
        $this->assertStatusCode(401, $client);

        // авторизуемся с правильным паролем но неправильный метод
        $user = new User('ats', $atsPassword);
        $this->loginAs($user, 'ats_area');
        $client = static::makeClient();
        $client->request('GET', $url);
        $this->assertStatusCode(405, $client);

        // отправляем успешное выполнение
        $client->request('POST', $url, [
            'callerId' => $callerId
        ]);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertTrue($jsonData['success']);
        $this->assertInternalType('array', $jsonData['receivers']);
        $this->assertCount(count($needUsers), $jsonData['receivers']);
        $this->assertCount(count($needUsers), array_intersect($jsonData['receivers'], $needUsers));
    }

    /**
     * Тестирование переотправки входящих звонков
     *
     * @covers IncomingCallsController::resendIncomingCallAction()
     */
    public function testResendIncomingCallAction()
    {
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('superadmin-user');
        /** @var UserEntity $unexpectedUser */
        $unexpectedUser = $this->fixtures->getReference('active-user');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        $url = $this->getUrl('incoming-calls.resend');

        $data = [
            'incoming_call_resend' => [
                'customer' => $customer->getId(),
                'callerId' => '74951111111',
                'comment' => 'testing comment',
            ]
        ];

        // неавторизованный пользователь должен получить 401
        $client = $this->createClient();
        $client->request('POST', $url, $data);
        $this->assertStatusCode(401, $client);

        // авторизуемся под контрагентом
        $this->loginAs($unexpectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url, $data);
        $this->assertStatusCode(403, $client);

        // авторизуемся под правильным пользователем но отправляем GET
        $this->loginAs($expectedUser, 'main');
        $client = static::makeClient();
        $client->request('GET', $url, $data);
        $this->assertStatusCode(405, $client);

        // теперь правильный запрос
        $client->request('POST', $url, $data);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertTrue($jsonData['submitted']);
        $this->assertTrue($jsonData['valid']);
        $this->assertTrue($jsonData['success']);
    }
}
