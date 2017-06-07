<?php

namespace AppBundle\Tests\Utils;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Utils\MailManager;
use AppBundle\Utils\UserNotificationManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use UserBundle\Entity\UserEntity;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование менеджера уведомлений для пользователя
 *
 * @package AppBundle\Tests\Utils
 */
class UserNotificationManagerTest extends WebTestCase
{
    /**
     * @var UserNotificationManager
     */
    protected $manager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        /** @var MailManager $mailManager */
        $mailManager = $this->getContainer()->get('mail_manager');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->manager = new UserNotificationManager($entityManager, $mailManager);
        $this->fixtures = $this->loadFixtures([UserTestFixture::class])->getReferenceRepository();
        $this->entityManager = $entityManager;
    }


    protected function createNotification(): UserNotificationEntity
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $entity = new UserNotificationEntity();

        $entity
            ->setType('testing type')
            ->setIsRead(false)
            ->setCreatedAt(new \DateTime())
            ->setReceiver($user);

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        return $entity;
    }

    /**
     * @covers UserNotificationManager::setAllNotificationIsRead()
     */
    public function testSetAllNotificationIsRead()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        // убедиться что на данный момент уведомлений нет
        $this->assertEquals(0, $this->manager->setAllNotificationIsRead($user));

        // создать уведомление и пометить его как прочтенное
        $this->createNotification();
        $this->createNotification();
        $this->assertEquals(2, $this->manager->setAllNotificationIsRead($user));

        // повторный вызов должен возвращать 0 - нет уведомлений для пометки
        $this->assertEquals(0, $this->manager->setAllNotificationIsRead($user));

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
