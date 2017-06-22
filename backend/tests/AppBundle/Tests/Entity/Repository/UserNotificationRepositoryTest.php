<?php

namespace AppBundle\Tests\Entity\Repository;


use AppBundle\Entity\UserNotificationEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\DataFixtures\ORM\UserNotificationTestFixture;
use UserBundle\Entity\UserEntity;
use Tests\DataFixtures\ORM\UserTestFixture;
use AppBundle\Entity\Repository\UserNotificationRepository;

/**
 * Тестирование репозитория уведомлений для пользователя
 *
 * @package AppBundle\Tests\Entity\Repository
 */
class UserNotificationRepositoryTest extends WebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var UserNotificationRepository
     */
    protected $repository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->repository = $this->entityManager->getRepository(UserNotificationEntity::class);
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class,
            UserNotificationTestFixture::class,
        ])->getReferenceRepository();

        $this->assertInstanceOf(UserNotificationRepository::class, $this->repository);
    }

    /**
     * @covers UserNotificationRepository::findAllUnreadUserNotification()
     */
    public function testFindAllUnreadUserNotification()
    {
        /** @var UserNotificationEntity $entity */
        $entity = $this->fixtures->getReference('active-user-notification');
        /** @var UserNotificationEntity $entitySecond */
        $entitySecond = $this->fixtures->getReference('active-user-notification-second');

        // убедиться что для неактивного пользователя уведомлений нет
        /** @var UserEntity $inactiveUser */
        $inactiveUser = $this->fixtures->getReference('inactive-user');
        $result = $this->repository->findAllUnreadUserNotification($inactiveUser);

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);

        // получить для активного пользователя непрочтенные уведомления
        $result = $this->repository->findAllUnreadUserNotification($entity->getReceiver());
        $this->assertInternalType('array', $result);
        $this->assertContainsOnlyInstancesOf(UserNotificationEntity::class, $result);
        $this->assertCount(2, $result);
        $founded = false;
        foreach ($result as $item) {
            $founded = in_array($item->getId(), [$entity->getId(), $entitySecond->getId()]);
        }
        $this->assertTrue($founded);

        // пометить сообщение как прочтенное
        $entity->setIsRead(true);
        $entitySecond->setIsRead(true);

        $this->entityManager->persist($entitySecond);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // убедиться что более непрочтенных уведомлений нет
        $result = $this->repository->findAllUnreadUserNotification($entity->getReceiver());
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
}
