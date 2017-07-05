<?php

namespace RentBundle\Tests\Controller;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Controller\RoomManagerController;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use Tests\AccessToActionTrait;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomRequestTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование контроллера редактирования комнат
 *
 * @package RentBundle\Tests\Controller
 */
class RoomManagerControllerTest extends WebTestCase
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
            UserTestFixture::class,
            CustomerTestFixture::class,
            ServiceTestFixture::class,
            RoomRequestTestFixture::class,
            RoomTestFixture::class
        ])->getReferenceRepository();
    }

    /**
     * @covers RoomManagerController::listAction()
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

        $url = $this->getUrl('room.manager.list');

        $result = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
        ], [
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
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
     * @covers RoomManagerController::createAction()
     */
    public function testCreateAction()
    {
        $url = $this->getUrl('room.manager.create');

        $result = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
        ], [
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
        ], [
            'room' => [
                'type' => RoomEntity::TYPE_MEETING,
                'title' => 'testing title',
            ],
        ], [
            'room' => [
                'type' => RoomEntity::TYPE_MEETING,
                'title' => 'testing title',
                'description' => 'testing description',
                'address' => 'testing address',
                'hourlyCost' => 100500
            ]
        ]);

        $this->assertArraySubset([
            'room' => [
                'type' => RoomEntity::TYPE_MEETING,
                'title' => 'testing title',
                'description' => 'testing description',
                'address' => 'testing address',
                'hourlyCost' => 100500,
                'schedule' => [],
                'scheduleBreak' => null,
                'holidays' => [],
                'workWeekends' => [],
                'requestPause' => null,
            ],
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => ''
        ], $result);
        $this->assertArrayHasKey('id', $result['room']);
        $this->assertGreaterThan(0, $result['room']['id']);
    }

    /**
     * @covers RoomManagerController::detailsAction()
     */
    public function testDetailsAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var RoomRequestEntity $expectedRequest */
        $expectedRequest = $this->fixtures->getReference('all-customer-everyday-room-request');

        $url = $this->getUrl('room.manager.details', [
            'id' => $room->getId(),
        ]);

        $result = $this->assertAccessToAction($url, 'GET', [
            'superadmin-user',
        ], [
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
        ]);

        $this->assertArraySubset([
            'room' => json_decode(json_encode($room), true),
            'requests' => [
                json_decode(json_encode($expectedRequest), true)
            ]
        ], $result);
    }

    /**
     * @covers RoomManagerController::updateAction()
     */
    public function testUpdateAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        $url = $this->getUrl('room.manager.update', [
            'id' => $room->getId(),
        ]);

        $type = RoomEntity::TYPE_CONFERENCE;
        $title = 'new title';
        $description = 'new description';
        $address = 'new address';
        $hourlyCost = 500.5;
        $schedule = [
            [
                [
                    'from' => '09:00',
                    'to' => '13:00',
                ],
                [
                    'from' => '14:00',
                    'to' => '18:00'
                ]
            ],
        ];
        $scheduleBreaks = [
            [
                'from' => '10:00',
                'to' => '11:00'
            ]
        ];
        $holidays = ['2017-09-01', '2017-03-08'];
        $workWeekends = ['2017-05-01', '2017-05-09'];
        $requestPause = 30;

        $result = $this->assertAccessToAction($url, 'POST', [
            'superadmin-user',
        ], [
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
        ], [
            'room' => [
            ]
        ], [
            'room' => [
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'address' => $address,
                'hourlyCost' => $hourlyCost,
                'schedule' => $schedule,
                'scheduleBreak' => $scheduleBreaks,
                'holidays' => $holidays,
                'workWeekends' => $workWeekends,
                'requestPause' => $requestPause
            ]
        ]);

        $room
            ->setType($type)
            ->setTitle($title)
            ->setDescription($description)
            ->setAddress($address)
            ->setHourlyCost($hourlyCost)
            ->setSchedule($schedule)
            ->setScheduleBreak($scheduleBreaks)
            ->setHolidays($holidays)
            ->setWorkWeekends($workWeekends)
            ->setRequestPause($requestPause);

        $this->assertArraySubset([
            'room' => json_decode(json_encode($room), true),
            'success' => true,
            'submitted' => true,
            'valid' => true,
            'validationErrors' => [],
            'firstError' => ''
        ], $result);
    }

    /**
     * @covers RoomManagerController::removeAction()
     */
    public function testRemoveAction()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        $url = $this->getUrl('room.manager.remove', [
            'id' => $room->getId()
        ]);

        $result = $this->assertAccessToAction($url, 'DELETE', [
            'superadmin-user',
        ], [
            'none-customer-user',
            'document-manager-user',
            'it-manager-user',
            'active-user',
        ]);

        $this->assertArraySubset([
            'id' => $room->getId(),
            'success' => true
        ], $result);
    }
}
