<?php

namespace AppBundle\Tests\Entity\Repository;


use AppBundle\Entity\UserNotificationEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use UserBundle\Entity\UserEntity;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;
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
        $this->fixtures = $this->loadFixtures([UserTestFixture::class])->getReferenceRepository();

        $this->assertInstanceOf(UserNotificationRepository::class, $this->repository);
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
     * @covers UserNotificationRepository::findAllUnreadUserNotification()
     */
    public function testFindAllUnreadUserNotification()
    {
        $entity = $this->createNotification();

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
        $this->assertCount(1, $result);
        $this->assertEquals($entity->getId(), $result[0]->getId());

        // пометить сообщение как прочтенное
        $entity->setIsRead(true);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // убедиться что более непрочтенных уведомлений нет
        $result = $this->repository->findAllUnreadUserNotification($entity->getReceiver());
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
}
