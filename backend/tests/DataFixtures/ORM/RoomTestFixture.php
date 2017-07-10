<?php

namespace Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RentBundle\Entity\RoomEntity;

/**
 * Фикстура помещений для бронирования
 *
 * @package Tests\DataFixtures\ORM
 */
class RoomTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // помещение работающее каждый день круглосуточно без выходных
        $everyDayRoom = new RoomEntity();

        $everyDayRoom
            ->setType(RoomEntity::TYPE_MEETING)
            ->setTitle('every day meeting room')
            ->setDescription('testing description')
            ->setAddress('testing address')
            ->setHourlyCost(100)
            ->setSchedule([])
            ->setScheduleBreak([])
            ->setHolidays([])
            ->setRequestPause(15);

        $mondayOnlyRoom = new RoomEntity();

        $mondayOnlyRoom
            ->setType(RoomEntity::TYPE_CONFERENCE)
            ->setTitle('monday only room')
            ->setDescription('testing description')
            ->setAddress('testing address')
            ->setHourlyCost(200)
            ->setSchedule([
                [
                    'avail' => true,
                    'schedule' => [
                        [
                            'from' => '14:00',
                            'to' => '18:00',
                        ]
                    ]
                ],
                [
                    'avail' => false,
                ],
                [
                    'avail' => false,
                ],
                [
                    'avail' => false,
                ],
                [
                    'avail' => false,
                ],
                [
                    'avail' => false,
                ],
                [
                    'avail' => false,
                ]
            ])
            ->setScheduleBreak([
                [
                    'from' => '15:00',
                    'to' => '17:00'
                ]
            ])
            ->setHolidays([
                (new \DateTime())->add(new \DateInterval('P1D'))->format('Y-m-d')
            ])
            ->setWorkWeekends([
                (new \DateTime())->add(new \DateInterval('P2D'))->format('Y-m-d')
            ])
            ->setRequestPause(15);

        $manager->persist($mondayOnlyRoom);
        $manager->persist($everyDayRoom);
        $manager->flush();

        $this->addReference('monday-only-room', $mondayOnlyRoom);
        $this->addReference('everyday-room', $everyDayRoom);
    }

    /**
     * Комнаты ни от чего не зависят
     *
     * @return int
     */
    public function getOrder()
    {
        return 0;
    }
}
