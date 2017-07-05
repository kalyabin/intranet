<?php

namespace RentBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use RentBundle\Entity\RoomEntity;

/**
 * Тестирование модели комнаты
 *
 * @package RentBundle\Tests
 */
class RoomEntityTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @covers RoomEntity::getId()
     * @covers RoomEntity::getTitle()
     * @covers RoomEntity::getDescription()
     * @covers RoomEntity::getType()
     * @covers RoomEntity::getAddress()
     * @covers RoomEntity::getHolidays()
     * @covers RoomEntity::getHourlyCost()
     * @covers RoomEntity::getRequestPause()
     * @covers RoomEntity::getSchedule()
     * @covers RoomEntity::getScheduleBreak()
     * @covers RoomEntity::getWorkWeekends()
     *
     * @covers RoomEntity::setTitle()
     * @covers RoomEntity::setDescription()
     * @covers RoomEntity::setType()
     * @covers RoomEntity::setAddress()
     * @covers RoomEntity::setHolidays()
     * @covers RoomEntity::setHourlyCost()
     * @covers RoomEntity::setRequestPause()
     * @covers RoomEntity::setSchedule()
     * @covers RoomEntity::setScheduleBreak()
     * @covers RoomEntity::setWorkWeekends()
     */
    public function testMe()
    {
        $title = 'testing title';
        $description = 'testing description';
        $type = RoomEntity::TYPE_MEETING;
        $address = 'testing address';
        $holidays = ['2017-01-01', '2017-01-02', '2017-01-03'];
        $workingWeekends = ['2017-05-01', '2017-05-09'];
        $hourlyCost = 2000;
        $requestPause = 15;
        $schedule = [
            [
                'weekday' => 0,
                'schedule' => [
                    [
                        'from' => '09:00',
                        'to' => '18:00',
                    ]
                ]
            ],
            [
                'weekday' => 1,
                'schedule' => [
                    [
                        'from' => '10:00',
                        'to' => '19:00'
                    ]
                ]
            ]
        ];
        $scheduleBreak = [
            'from' => '14:00',
            'to' => '15:00'
        ];

        $entity = new RoomEntity();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getTitle());
        $this->assertNull($entity->getDescription());
        $this->assertNull($entity->getType());
        $this->assertNull($entity->getAddress());
        $this->assertNull($entity->getHolidays());
        $this->assertNull($entity->getHourlyCost());
        $this->assertNull($entity->getRequestPause());
        $this->assertNull($entity->getSchedule());
        $this->assertNull($entity->getScheduleBreak());
        $this->assertNull($entity->getWorkWeekends());

        $entity
            ->setTitle($title)
            ->setDescription($description)
            ->setType($type)
            ->setAddress($address)
            ->setHolidays($holidays)
            ->setHourlyCost($hourlyCost)
            ->setRequestPause($requestPause)
            ->setSchedule($schedule)
            ->setScheduleBreak($scheduleBreak)
            ->setWorkWeekends($workingWeekends);

        $this->assertEquals($title, $entity->getTitle());
        $this->assertEquals($description, $entity->getDescription());
        $this->assertEquals($type, $entity->getType());
        $this->assertEquals($address, $entity->getAddress());
        $this->assertArraySubset($holidays, $entity->getHolidays());
        $this->assertEquals($hourlyCost, $entity->getHourlyCost());
        $this->assertEquals($requestPause, $entity->getRequestPause());
        $this->assertArraySubset($schedule, $entity->getSchedule());
        $this->assertArraySubset($scheduleBreak, $entity->getScheduleBreak());
        $this->assertArraySubset($workingWeekends, $entity->getWorkWeekends());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertGreaterThan(0, $entity->getId());

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
