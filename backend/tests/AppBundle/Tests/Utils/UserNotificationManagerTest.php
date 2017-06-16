<?php

namespace AppBundle\Tests\Utils;


use AppBundle\Entity\UserNotificationEntity;
use AppBundle\Service\CometClient;
use AppBundle\Utils\MailManager;
use AppBundle\Utils\UserNotificationManager;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\UserNotificationTestFixture;
use UserBundle\Entity\UserEntity;
use Tests\DataFixtures\ORM\UserTestFixture;

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

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->manager = new UserNotificationManager($entityManager);
        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            UserNotificationTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository();
        $this->entityManager = $entityManager;
    }

    /**
     * @covers UserNotificationManager::setAllNotificationIsRead()
     */
    public function testSetAllNotificationIsRead()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        // создать уведомление и пометить его как прочтенное
        $this->assertEquals(2, $this->manager->setAllNotificationIsRead($user));

        // повторный вызов должен возвращать 0 - нет уведомлений для пометки
        $this->assertEquals(0, $this->manager->setAllNotificationIsRead($user));

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
