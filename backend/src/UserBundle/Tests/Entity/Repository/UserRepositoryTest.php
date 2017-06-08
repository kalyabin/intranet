<?php

namespace UserBunde\Tests\Entity\Repository;

use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\Repository\UserRepository;


/**
 * Тестирование класса UserRepository
 *
 * @package UserBundle\Tests\Entity\Repository
 */
class UserRepositoryTest extends WebTestCase
{
    /**
     * @var \UserBundle\Entity\Repository\UserRepository
     */
    protected $repository;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    protected function setUp()
    {
        static::bootKernel();

        $this->repository = static::$kernel->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(UserEntity::class);

        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository();
    }

    /**
     * Получение пользователя по идентификатору
     *
     * @covers UserRepository::findOneById()
     */
    public function testFindOneById()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $expectedUser = $this->repository->findOneById($user->getId());

        $this->assertNotNull($expectedUser);
        $this->assertInstanceOf(UserEntity::class, $expectedUser);
        $this->assertEquals($expectedUser->getId(), $user->getId());

        $unexpectedUser = $this->repository->findOneById(0);
        $this->assertNull($unexpectedUser);
    }

    /**
     * Получение пользователя по e-mail
     *
     * @covers UserRepository::findOneByEmail()
     */
    public function testFindOneByEmail()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $expectedUser = $this->repository->findOneByEmail($user->getEmail());

        $this->assertNotNull($expectedUser);
        $this->assertInstanceOf(UserEntity::class, $expectedUser);
        $this->assertEquals($expectedUser->getId(), $user->getId());

        $unexpectedUser = $this->repository->findOneByEmail('non-existent@email.ru');
        $this->assertNull($unexpectedUser);
    }

    /**
     * Тестирование существования пользователя по e-mail
     *
     * @covers UserRepository::userIsExistsByEmail()
     */
    public function testUserIsExistsByEmail()
    {
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');

        $this->assertTrue($this->repository->userIsExistsByEmail($user->getEmail()));
        $this->assertFalse($this->repository->userIsExistsByEmail('non-existent@email.ru'));
        $this->assertFalse($this->repository->userIsExistsByEmail($user->getEmail(), $user->getId()));
    }

    /**
     * Тестирование получения общего количества пользователей
     *
     * @covers UserRepository::getTotalCount()
     */
    public function testGetTotalCount()
    {
        /** @var UserEntity[] $users */
        $expectedCount = 0;
        foreach ($this->fixtures->getReferences() as $reference) {
            if ($reference instanceof UserEntity) {
                $expectedCount++;
            }
        }

        $count = $this->repository->getTotalCount();

        $this->assertGreaterThan(0, $expectedCount);
        $this->assertEquals($expectedCount, $count);
    }

    /**
     * Тестирование получения пользователей по ролям
     *
     * @covers UserRepository::findByRole()
     */
    public function testFindByRole()
    {
        /** @var UserEntity $superadmin */
        $superadmin = $this->fixtures->getReference('superadmin-user');

        // поиск заблокированных администраторов
        $result = $this->repository->findByRole('ROLE_SUPERADMIN', UserEntity::STATUS_LOCKED);

        $this->assertInstanceOf(IterableResult::class, $result);

        foreach ($result as $rows) {
            $this->assertInternalType('array', $rows);
            $this->assertCount(0, $rows);
        }

        // поиск активных администраторов
        $result = $this->repository->findByRole('ROLE_SUPERADMIN', UserEntity::STATUS_ACTIVE);

        $this->assertInstanceOf(IterableResult::class, $result);

        $iterates = 0;
        $founded = false;

        foreach ($result as $rows) {
            $this->assertInternalType('array', $rows);
            $this->assertCount(1, $rows);

            foreach ($rows as $item) {
                $iterates++;
                /** @var UserEntity $item */
                $this->assertInstanceOf(UserEntity::class, $item);
                if ($superadmin->getId() == $item->getId()) {
                    $founded = true;
                }
            }
        }

        // наш супер админ был найден
        $this->assertTrue($founded);

        // в фикстурах есть только 2 супер админа
        $this->assertEquals(1, $iterates);
    }
}
