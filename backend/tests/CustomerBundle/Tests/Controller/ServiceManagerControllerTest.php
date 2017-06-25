<?php

namespace CustomerBundle\Tests\Controller;

use CustomerBundle\Controller\ServiceManagerController;
use CustomerBundle\Entity\Repository\ServiceRepository;
use CustomerBundle\Entity\ServiceEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AccessToActionTrait;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование контроллера управления дополнительными услугами
 *
 * @package CustomerBundle\Tests\Controller
 */
class ServiceManagerControllerTest extends WebTestCase
{
    use AccessToActionTrait;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers ServiceManagerController::listAction()
     */
    public function testListAction()
    {
        /** @var ServiceEntity $itDepartment */
        $itDepartment = $this->fixtures->getReference('service-it');
        /** @var ServiceEntity $bookerDepartment */
        $bookerDepartment = $this->fixtures->getReference('service-booker');

        $url = $this->getUrl('service.manager.list');

        $result = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
        ], [
            'active-user',
            'it-manager-user',
        ]);

        $this->assertArrayHasKey('list', $result);
        $this->assertCount(2, $result['list']);

        foreach ($result['list'] as $item) {
            $this->assertArrayHasKey('id', $item);
            if ($item['id'] == $itDepartment->getId()) {
                $this->assertArraySubset($item, json_decode(json_encode($itDepartment), true));
            } else {
                $this->assertArraySubset($item, json_decode(json_encode($bookerDepartment), true));
            }
        }
    }

    /**
     * @covers ServiceManagerController::createAction()
     */
    public function testCreateAction()
    {
        $url = $this->getUrl('service.manager.create');

        $validData = [
            'service' => [
                'id' => 'test-department',
                'title' => 'Test department',
                'enableCustomerRole' => 'ROLE_MAINTAINCE_CUSTOMER'
            ]
        ];

        $invalidData = [
            'service' => [
                'id' => 'it-department',
                'title' => 'IT-department',
                'enableCustomerRole' => 'ROLE_IT_CUSTOMER',
            ]
        ];

        $result = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
        ], [
            'active-user',
            'it-manager-user',
        ], $invalidData, $validData);

        $this->assertArraySubset([
            'service' => [
                'id' => 'test-department',
                'isActive' => false,
                'title' => 'Test department',
                'description' => '',
                'customerRole' => 'ROLE_MAINTAINCE_CUSTOMER',
                'tariff' => [],
            ],
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => '',
        ], $result);
    }

    /**
     * @covers ServiceManagerController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var ServiceEntity $itDepartment */
        $itDepartment = $this->fixtures->getReference('service-it');

        $url = $this->getUrl('service.manager.details', [
            'id' => $itDepartment->getId()
        ]);

        $result = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
        ], [
            'active-user',
            'it-manager-user'
        ]);

        $this->assertArraySubset([
            'service' => json_decode(json_encode($itDepartment), true)
        ], $result);
    }

    /**
     * @covers ServiceManagerController::updateAction()
     */
    public function testUpdateAction()
    {
        /** @var ServiceEntity $itDepartment */
        $itDepartment = $this->fixtures->getReference('service-it');

        $url = $this->getUrl('service.manager.update', [
            'id' => $itDepartment->getId()
        ]);

        $validData = [
            'service' => [
                'id' => 'it-department',
                'title' => 'test update',
                'isActive' => false,
                'description' => 'test update',
                'enableCustomerRole' => 'ROLE_MAINTAINCE_CUSTOMER',
            ]
        ];

        $invalidData = [
            'service' => [
                'id' => 'wrong department identified',
                'title' => 'test update',
                'isActive' => false,
                'description' => 'test update'
            ]
        ];

        $result = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
        ], [
            'active-user',
            'it-manager-user'
        ], $invalidData, $validData);

        $this->assertArraySubset([
            'service' => [
                'id' => 'it-department',
                'title' => 'test update',
                'description' => 'test update',
                'customerRole' => 'ROLE_MAINTAINCE_CUSTOMER',
                'isActive' => false,
                'tariff' => [],
            ],
            'success' => true,
            'valid' => true,
            'submitted' => true,
            'validationErrors' => [],
            'firstError' => '',
        ], $result);
    }

    /**
     * @covers ServiceManagerController::deleteAction()
     */
    public function testDeleteAction()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var ServiceRepository $repository */
        $repository = $entityManager->getRepository(ServiceEntity::class);
        /** @var ServiceEntity $service */
        $service = $this->fixtures->getReference('service-it');

        $url = $this->getUrl('service.manager.delete', [
            'id' => $service->getId()
        ]);

        $result = $this->assertAccessToAction($url, 'DELETE', [
            'superadmin-user',
        ], [
            'active-user',
            'it-manager-user'
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $expected = $repository->findOneById('it-department');
        $this->assertNull($expected);
    }
}
