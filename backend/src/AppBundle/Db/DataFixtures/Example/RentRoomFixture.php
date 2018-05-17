<?php

namespace AppBundle\Db\DataFixtures\Example;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use RentBundle\Entity\RoomEntity;

/**
 * Фикстуры комнат для аренды для примера
 *
 * @package AppBundle\Db\DataFixtures\Example
 */
class RentRoomFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $room1 = (new RoomEntity())
            ->setType(RoomEntity::TYPE_MEETING)
            ->setTitle('Переговорная комната')
            ->setDescription('описание помещения')
            ->setAddress('расположение помещения')
            ->setHourlyCost(300)
            ->setRequestPause(15);

        $room2 = (new RoomEntity())
            ->setType(RoomEntity::TYPE_CONFERENCE)
            ->setTitle('Конференц-зал')
            ->setDescription('описание помещения')
            ->setAddress('расположение помещения')
            ->setHourlyCost(2000)
            ->setRequestPause(30);

        $manager->persist($room1);
        $manager->persist($room2);

        $manager->flush();

        $this->addReference('room1', $room1);
        $this->addReference('room2', $room2);
    }
}
