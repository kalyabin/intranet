<?php

namespace Tests\DataFixtures\ORM;


use AppBundle\Entity\UserNotificationEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\UserEntity;

/**
 * Модель нотификации для пользователя
 *
 * @package Tests\DataFixtures\ORM
 */
class UserNotificationTestFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var UserEntity $user */
        $user = $this->getReference('active-user');

        $entity = new UserNotificationEntity();

        $entity
            ->setType('testing type')
            ->setIsRead(false)
            ->setCreatedAt(new \DateTime())
            ->setReceiver($user);

        $entitySecond = new UserNotificationEntity();

        $entitySecond
            ->setType('testing type 2')
            ->setIsRead(false)
            ->setCreatedAt(new \DateTime())
            ->setReceiver($user);

        $manager->persist($entitySecond);
        $manager->persist($entity);
        $manager->flush();

        $this->addReference('active-user-notification', $entity);
        $this->addReference('active-user-notification-second', $entitySecond);
    }

    /**
     * Тест Зависит от пользователей
     *
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}
