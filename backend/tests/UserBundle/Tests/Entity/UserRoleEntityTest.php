<?php

namespace UserBunde\Tests\Entity;

use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;
use Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование UserRoleEntity
 *
 * @package UserBunde\Tests\Entity
 */
class UserRoleEntityTest extends WebTestCase
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @covers UserRoleEntity::setUser()
     * @covers UserRoleEntity::setCode()
     *
     * @covers UserRoleEntity::getUser()
     * @covers UserRoleEntity::getCode()
     *
     * @covers UserEntity::addRole()
     * @covers UserEntity::removeRole()
     * @covers UserEntity::getRole()
     */
    public function testMe()
    {
        /** @var UserEntity $user */
        $user = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class
        ])->getReferenceRepository()->getReference('active-user');

        $role = new UserRoleEntity();

        $role
            ->setUser($user)
            ->setCode('ROLE_SUPERADMIN');

        $this->em->persist($role);
        $this->em->flush();

        $this->assertInstanceOf(UserEntity::class, $role->getUser());
        $this->assertEquals('ROLE_SUPERADMIN', $role->getCode());

        // установка ролей через модель user
        $role = new UserRoleEntity();
        $role->setCode('USER');
        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals(3, $user->getRole()->count());

        $user->removeRole($role);

        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals(2, $user->getRole()->count());

        $user->clearRoles();

        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals(0, $user->getRole()->count());
    }
}
