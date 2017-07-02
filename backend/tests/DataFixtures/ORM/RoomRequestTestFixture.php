<?php

namespace Tests\DataFixtures\ORM;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;

/**
 * Фикстуры заявок на аренду помещений
 *
 * @package Tests\DataFixtures\ORM
 */
class RoomRequestTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var RoomEntity $everyDayRoom */
        $everyDayRoom = $this->getReference('everyday-room');
        /** @var CustomerEntity $allCustomer */
        $allCustomer = $this->getReference('all-customer');

        $entity = new RoomRequestEntity();

        $entity
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allCustomer)
            ->setRoom($everyDayRoom)
            ->setCustomerComment('testing comment')
            ->setFrom((new \DateTime())->add(new \DateInterval('P1D')))
            ->setTo((new \DateTime())->add(new \DateInterval('P2D')))
            ->setStatus(RoomRequestEntity::STATUS_PENDING);

        $manager->persist($entity);

        $manager->flush();

        $this->addReference('all-customer-everyday-room-request', $entity);
    }

    /**
     * Заявки на аренду зависят от комнат и арендаторов
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
