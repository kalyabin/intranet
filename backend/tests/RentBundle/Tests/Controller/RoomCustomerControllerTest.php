<?php

namespace RentBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Controller\RoomCustomerController;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use Tests\AccessToActionTrait;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomRequestTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Entity\UserEntity;

/**
 * Тестирование работы с помещениями арендатором
 *
 * @package RentBundle\Tests\Controller
 */
class RoomCustomerControllerTest extends WebTestCase
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
            CustomerTestFixture::class,
            UserTestFixture::class,
            ServiceTestFixture::class,
            RoomTestFixture::class,
            RoomRequestTestFixture::class,
        ])->getReferenceRepository();
    }

    /**
     * @covers RoomCustomerController::listAction()
     */
    public function testListAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        $roomsCnt = 0;
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof RoomEntity) {
                $roomsCnt++;
            }
        }

        $url = $this->getUrl('room.customer.list');

        $result = $this->assertAccessToAction($url, 'GET', [
            'none-customer-user',
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        $this->assertArraySubset([
            'pageNum' => 0,
            'totalCount' => $roomsCnt,
            'pageSize' => $roomsCnt,
        ], $result);
        $this->assertArrayHasKey('list', $result);
        $this->assertInternalType('array', $result);
        $this->assertCount($roomsCnt, $result['list']);

        foreach ($result['list'] as $item) {
            $this->assertArrayHasKey('id', $item);
            if ($item['id'] == $room->getId()) {
                $this->assertArraySubset(json_decode(json_encode($room), true), $item);
            }
        }
    }

    /**
     * @covers RoomCustomerController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var RoomRequestEntity $expectedRequest */
        $expectedRequest = $this->fixtures->getReference('all-customer-everyday-room-request');

        $url = $this->getUrl('room.customer.details', [
            'id' => $room->getId()
        ]);

        // проверка для пользователя, у которого есть заявки на эту комнату
        $result = $this->assertAccessToAction($url, 'GET', [
            'none-customer-user',
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        $this->assertArraySubset([
            'room' => json_decode(json_encode($room), true),
            'myRequests' => [
                json_decode(json_encode($expectedRequest), true)
            ],
            'reserved' => []
        ], $result);

        // проверка для пользователя, у которого нет заявок на эту комнату
        $result = $this->assertAccessToAction($url, 'GET', [
            'active-user',
            'none-customer-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        $this->assertArraySubset([
            'room' => json_decode(json_encode($room), true),
            'myRequests' => [],
            'reserved' => [
                [
                    'from' => $expectedRequest->getFrom()->format('Y-m-d H:i'),
                    'to' => $expectedRequest->getTo()->format('Y-m-d H:i')
                ]
            ]
        ], $result);
    }

    /**
     * @covers RoomCustomerController::actualRequestListAction()
     */
    public function testActualRequestListAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('active-user');

        $url = $this->getUrl('room.customer.request_list');

        // тестирование для пользователя у которого нет заявок
        $result = $this->assertAccessToAction($url, 'GET', [
            'active-user',
            'none-customer-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);
        $this->assertArraySubset([
            'list' => [],
            'pageSize' => 0,
            'pageNum' => 0,
            'totalCount' => 0,
        ], $result);

        // тестирование для пользователя у которого есть заявки
        $requestCnt = 0;
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof RoomRequestEntity) {
                $requestCnt++;
            }
        }
        $result = $this->assertAccessToAction($url, 'GET', [
            'none-customer-user',
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);
        $this->assertArraySubset([
            'pageSize' => $requestCnt,
            'pageNum' => 0,
            'totalCount' => $requestCnt,
        ], $result);
        $this->assertArrayHasKey('list', $result);
        $this->assertNotEmpty($result['list']);
        foreach ($result['list'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertGreaterThan(0, $item['id']);
            $this->assertArrayHasKey('customer', $item);
            $this->assertInternalType('array', $item['customer']);
            $this->assertArrayHasKey('id', $item['customer']);
            $this->assertEquals($expectedUser->getCustomer()->getId(), $item['customer']['id']);
            $this->assertArrayHasKey('room', $item);
            $this->assertArraySubset(json_decode(json_encode($room), true), $item['room']);
        }
    }

    /**
     * @covers RoomCustomerController::createRequestAction()
     */
    public function testCreateRequestAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var UserEntity $expectedUser */
        $expectedUser = $this->fixtures->getReference('active-user');

        $url = $this->getUrl('room.customer.request_create');

        $from = date('Y-m-d H:i', time() + 86400);
        $to = date('Y-m-d H:i', time() + 86400 + 86400);
        $comment = 'testing comment for customer';

        $result = $this->assertAccessToAction($url, 'POST', [
            'none-customer-user',
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ], [
            'room_request' => []
        ], [
            'room_request' => [
                'room' => $room->getId(),
                'from' => $from,
                'to' => $to,
                'customerComment' => $comment
            ]
        ]);

        $this->assertArraySubset([
            'success' => true,
            'request' => [
                'status' => RoomRequestEntity::STATUS_PENDING,
                'room' => json_decode(json_encode($room), true),
                'customer' => json_decode(json_encode($expectedUser->getCustomer()), true),
                'from' => $from,
                'to' => $to,
                'managerComment' => null,
                'customerComment' => $comment
            ],
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => ''
        ], $result);

        $this->assertArrayHasKey('id', $result['request']);
        $this->assertGreaterThan(0, $result['request']['id']);
    }

    /**
     * @covers RoomCustomerController::cancelRequestAction()
     */
    public function testCancelRequestAction()
    {
        /** @var RoomRequestEntity $expectedRequest */
        $expectedRequest = $this->fixtures->getReference('all-customer-everyday-room-request');

        $url = $this->getUrl('room.customer.request_cancel', [
            'id' => $expectedRequest->getId()
        ]);

        $result = $this->assertAccessToAction($url, 'DELETE', [
            'active-user',
        ], [
            'document-manager-user',
            'it-manager-user',
            'superadmin-user',
        ]);

        $expectedRequest->setStatus(RoomRequestEntity::STATUS_CANCELED);

        $this->assertArraySubset([
            'request' => json_decode(json_encode($expectedRequest), true),
            'success' => true
        ], $result);
    }
}
