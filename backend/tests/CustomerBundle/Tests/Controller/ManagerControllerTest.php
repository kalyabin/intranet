<?php

namespace CustomerBundle\Tests\Controller;

use CustomerBundle\Controller\ManagerController;
use CustomerBundle\Entity\CustomerEntity;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\JsonResponseTestTrait;
use Tests\ManagerControllerTestTrait;
use UserBundle\Entity\UserEntity;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование класса ManagerController
 *
 * @package CustomerBundle\Tests\Controller
 */
class ManagerControllerTest extends WebTestCase
{
    use JsonResponseTestTrait;
    use ManagerControllerTestTrait;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            UserTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers ManagerController::listAction()
     */
    public function testListAction()
    {
        /** @var UserEntity $superadminUser */
        $superadminUser = $this->fixtures->getReference('superadmin-user');

        // подсчитать общее количество всех контрагентов
        $expectedCount = [];
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof CustomerEntity && !in_array($reference->getId(), $expectedCount)) {
                $expectedCount[] = $reference->getId();
            }
        }
        $expectedCount = count($expectedCount);

        $pageSize = 1;
        $pageNum = 2;

        $url = $this->getUrl('customer.manager.list', [
            'pageSize' => $pageSize,
            'pageNum' => $pageNum,
        ]);

        // авторизоваться под админом
        $this->loginAs($superadminUser, 'main');

        $client = static::makeClient();

        $client->request('GET', $url);
        $this->assertStatusCode(200, $client);
        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());
        $this->assertNotEmpty($jsonData);
        $this->assertArrayHasKey('pageSize', $jsonData);
        $this->assertEquals($jsonData['pageSize'], $pageSize);
        $this->assertArrayHasKey('pageNum', $jsonData);
        $this->assertEquals($jsonData['pageNum'], $pageNum);
        $this->assertArrayHasKey('totalCount', $jsonData);
        $this->assertEquals($jsonData['totalCount'], $expectedCount);
        $this->assertInternalType('array', $jsonData['list']);
        $this->assertNotEmpty($jsonData['list']);
        foreach ($jsonData['list'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertGreaterThan(0, $item['id']);
            $this->assertArrayHasKey('name', $item);
            $this->assertNotEmpty($item['name']);
        }
    }

    /**
     * @covers ManagerController::createAction()
     */
    public function testCreateAction()
    {
        /** @var UserEntity $superadminUser */
        $superadminUser = $this->fixtures->getReference('superadmin-user');

        $url = $this->getUrl('customer.manager.create');

        $this->assertNonAuthenticatedUsers('POST', $url);

        $invalidPostData = [
            'customer' => []
        ];

        $validPostData = [
            'customer' => [
                'name' => 'testing customer',
                'currentAgreement' => 'testing agreement',
                'allowItDepartment' => true,
                'allowBookerDepartment' => true,
            ]
        ];

        $this->loginAs($superadminUser, 'main');

        $client = static::makeClient();

        $client->request('POST', $url, $invalidPostData);
        $this->assertStatusCode(400, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'valid' => false,
            'submitted' => true,
        ], $jsonData);
        $this->assertInternalType('array', $jsonData['validationErrors']);
        $this->assertNotEmpty($jsonData['validationErrors']);

        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(200, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => true,
            'valid' => true,
            'submitted' => true,
        ], $jsonData);
        $this->assertInternalType('array', $jsonData['validationErrors']);
        $this->assertEmpty($jsonData['validationErrors']);

        $this->assertInternalType('array', $jsonData['customer']);
        $this->assertGreaterThan(0, $jsonData['customer']['id']);
        $this->assertEquals('testing customer', $jsonData['customer']['name']);
        $this->assertEquals('testing agreement', $jsonData['customer']['currentAgreement']);
    }

    /**
     * @covers ManagerController::updateAction()
     */
    public function testUpdateAction()
    {
        /** @var UserEntity $superadminUser */
        $superadminUser = $this->fixtures->getReference('superadmin-user');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        $url = $this->getUrl('customer.manager.update', [
            'id' => $customer->getId()
        ]);

        $this->assertNonAuthenticatedUsers('POST', $url);

        $this->assertTrue($customer->getAllowBookerDepartment());
        $this->assertTrue($customer->getAllowItDepartment());

        $invalidPostData = [
            'customer' => []
        ];

        $validPostData = [
            'customer' => [
                'name' => 'testing customer',
                'currentAgreement' => 'testing agreement',
                'allowItDepartment' => false,
                'allowBookerDepartment' => false,
            ]
        ];

        $this->loginAs($superadminUser, 'main');

        $client = static::makeClient();

        $client->request('POST', $url, $invalidPostData);
        $this->assertStatusCode(400, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'success' => false,
            'valid' => false,
            'submitted' => true,
        ], $jsonData);
        $this->assertInternalType('array', $jsonData['validationErrors']);
        $this->assertNotEmpty($jsonData['validationErrors']);

        $client->request('POST', $url, $validPostData);
        $this->assertStatusCode(200, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $customerData = $customer->jsonSerialize();
        $customerData['allowItDepartment'] = false;
        $customerData['allowBookerDepartment'] = false;

        $this->assertArraySubset([
            'customer' => $customerData,
            'success' => true,
            'valid' => true,
            'submitted' => true,
        ], $jsonData);
        $this->assertInternalType('array', $jsonData['validationErrors']);
        $this->assertEmpty($jsonData['validationErrors']);
    }

    /**
     * @covers ManagerController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var UserEntity $superadminUser */
        $superadminUser = $this->fixtures->getReference('superadmin-user');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        $url = $this->getUrl('customer.manager.details', [
            'id' => $customer->getId()
        ]);

        $this->assertNonAuthenticatedUsers('GET', $url);

        $this->loginAs($superadminUser, 'main');

        $client = static::makeClient();

        $client->request('GET', $url);
        $this->assertStatusCode(200, $client);

        $jsonData = $this->assertIsValidJsonResponse($client->getResponse());

        $this->assertArraySubset([
            'customer' => json_decode(json_encode($customer), true)
        ], $jsonData);
    }
}
