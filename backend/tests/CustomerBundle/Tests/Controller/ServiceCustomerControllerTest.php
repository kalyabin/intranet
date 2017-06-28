<?php

namespace CustomerBundle\Tests\Controller;

use CustomerBundle\Controller\ServiceCustomerController;
use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Entity\Repository\CustomerRepository;
use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Entity\ServiceTariffEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AccessToActionTrait;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование управления собственными услугами от арендатора
 *
 * @package CustomerBundle\Tests\Controller
 */
class ServiceCustomerControllerTest extends WebTestCase
{
    use AccessToActionTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            UserTestFixture::class,
            CustomerTestFixture::class
        ])->getReferenceRepository();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->customerRepository = $entityManager->getRepository(CustomerEntity::class);
    }

    /**
     * @covers ServiceCustomerController::listAction()
     */
    public function testListAction()
    {
        // получить список доступных услуг
        /** @var ServiceEntity $itService */
        $itService = $this->fixtures->getReference('service-it');
        /** @var ServiceEntity $bookerService */
        $bookerService = $this->fixtures->getReference('service-booker');

        $url = $this->getUrl('service.customer.list');

        $result = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
        ], []);

        $this->assertArrayHasKey('pageSize', $result);
        $this->assertArrayHasKey('pageNum', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertArrayHasKey('list', $result);

        $this->assertEquals(2, $result['pageSize']);
        $this->assertEquals(0, $result['pageNum']);
        $this->assertEquals(2, $result['totalCount']);
        $this->assertInternalType('array', $result['list']);
        $this->assertCount(2, $result['list']);

        foreach ($result['list'] as $item) {
            $this->assertArrayHasKey('id', $item);
            if ($item['id'] == $itService->getId()) {
                $this->assertArraySubset(json_decode(json_encode($itService), true), $item);
            } else {
                $this->assertArraySubset(json_decode(json_encode($bookerService), true), $item);
            }
        }
    }

    /**
     * @covers ServiceCustomerController::activatedListAction()
     */
    public function testActivatedListAction()
    {
        // получить список активированных услуг для арендатора
        /** @var ServiceEntity $itService */
        $itService = $this->fixtures->getReference('service-it');
        /** @var ServiceEntity $bookerService */
        $bookerService = $this->fixtures->getReference('service-booker');

        $url = $this->getUrl('service.customer.activated_list');

        $result = $this->assertAccessToAction($url, 'GET', [
            'none-customer-user',
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        $this->assertArrayHasKey('pageSize', $result);
        $this->assertArrayHasKey('pageNum', $result);
        $this->assertArrayHasKey('totalCount', $result);
        $this->assertArrayHasKey('list', $result);

        $this->assertEquals(2, $result['pageSize']);
        $this->assertEquals(0, $result['pageNum']);
        $this->assertEquals(2, $result['totalCount']);
        $this->assertInternalType('array', $result['list']);
        $this->assertCount(2, $result['list']);

        foreach ($result['list'] as $item) {
            $this->assertArrayHasKey('service', $item);
            $this->assertArrayHasKey('id', $item['service']);
            $service = $item['service'];
            if ($service['id'] == $itService->getId()) {
                $this->assertArraySubset(json_decode(json_encode($itService), true), $service);
            } else {
                $this->assertArraySubset(json_decode(json_encode($bookerService), true), $service);
            }
        }
    }

    /**
     * @covers ServiceCustomerController::activateAction()
     */
    public function testActivateAction()
    {
        /** @var ServiceEntity $itService */
        $itService = $this->fixtures->getReference('service-it');
        /** @var ServiceTariffEntity $itTariff */
        $itTariff = $this->fixtures->getReference('service-it-tariff');
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('none-customer-user');
        /** @var UserEntity $unexpectedUser */
        $unexpectedUser = $this->fixtures->getReference('active-user');

        $url = $this->getUrl('service.customer.activate', [
            'id' => $itService->getId()
        ]);

        // менеджеры не могут активировать себе услуги
        $this->assertAccessToAction($url, 'POST', [], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        // попытка активировать уже активированную услугу
        $this->loginAs($unexpectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url, [
            'tariff' => $itTariff->getId()
        ]);
        $this->assertStatusCode(400, $client);

        // попытка активировать услугу без указания тарифа
        $this->loginAs($expectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);

        // указание невалидного тарифа
        $client->request('POST', $url, [
            'tariff' => -1
        ]);
        $this->assertStatusCode(400, $client);

        // активация тарифа
        $client->request('POST', $url, [
            'tariff' => $itTariff->getId()
        ]);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'activated' => [
                'service' => json_decode(json_encode($itService), true),
                'tariff' => json_decode(json_encode($itTariff), true),
            ],
            'success' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => '',
        ], $jsonData);
    }

    /**
     * @covers ServiceCustomerController::deactivateAction()
     */
    public function testDeactivateAction()
    {
        /** @var ServiceEntity $itService */
        $itService = $this->fixtures->getReference('service-it');
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('active-user');
        /** @var UserEntity $unexpectedUser */
        $unexpectedUser = $this->fixtures->getReference('none-customer-user');

        $url = $this->getUrl('service.customer.deactivate', [
            'id' => $itService->getId()
        ]);

        // менеджеры не могут деактивировать себе услуги
        $this->assertAccessToAction($url, 'POST', [], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        // попытка деактивировать неактивированную услугу
        $this->loginAs($unexpectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url);
        $this->assertStatusCode(400, $client);

        // нормальная деактивация услуги
        $this->loginAs($expectedUser, 'main');
        $client = static::makeClient();
        $client->request('POST', $url);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'service' => json_decode(json_encode($itService), true),
            'success' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => '',
        ], $jsonData);
    }
}
