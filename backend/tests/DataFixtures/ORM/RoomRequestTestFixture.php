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

        // актуальная заявка
        $allCustomerRequest = new RoomRequestEntity();
        $allCustomerRequest
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allCustomer)
            ->setRoom($everyDayRoom)
            ->setCustomerComment('testing comment')
            ->setFrom((new \DateTime())->add(new \DateInterval('P1D')))
            ->setTo((new \DateTime())->add(new \DateInterval('P2D')))
            ->setStatus(RoomRequestEntity::STATUS_PENDING);

        // устаревшая заявка
        $oldRequest = new RoomRequestEntity();
        $oldRequest
            ->setCreatedAt(new \DateTime())
            ->setCustomer($allCustomer)
            ->setRoom($everyDayRoom)
            ->setCustomerComment('testing comment')
            ->setFrom((new \DateTime())->sub(new \DateInterval('P3M')))
            ->setTo((new \DateTime())->sub(new \DateInterval('P3M'))->add(new \DateInterval('P1D')))
            ->setStatus(RoomRequestEntity::STATUS_APPROVED);

        $manager->persist($oldRequest);
        $manager->persist($allCustomerRequest);

        $manager->flush();

        $this->addReference('all-customer-everyday-room-request', $allCustomerRequest);
        $this->addReference('all-customer-everyday-room-old-request', $oldRequest);
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
