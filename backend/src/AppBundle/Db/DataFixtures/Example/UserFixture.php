<?php

namespace AppBundle\Db\DataFixtures\Example;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\UserEntity;
use UserBundle\Entity\UserRoleEntity;
use UserBundle\Utils\UserManager;

/**
 * Фикстуры пользователей
 *
 * @package AppBundle\Db\DataFixtures\Example
 */
class UserFixture extends Fixture
{
    /**
     * @var UserManager
     */
    private $userService;

    public function __construct(UserManager $userService)
    {
        $this->userService = $userService;
    }

    public function load(ObjectManager $manager)
    {
        /** @var CustomerEntity $customer1 */
        $customer1 = $this->getReference('customer1');
        /** @var CustomerEntity $customer2 */
        $customer2 = $this->getReference('customer2');
        /** @var CustomerEntity $customer3 */
        $customer3 = $this->getReference('customer3');

        $superadminRole = (new UserRoleEntity())->setCode('ROLE_SUPERADMIN');
        $superadmin = (new UserEntity())
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('Тестовый СуперАдмин')
            ->setPassword('testingpassword')
            ->addRole($superadminRole)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->generateSalt();

        $this->userService->encodeUserPassword($superadmin, $superadmin->getPassword());

        $customer1UserRole = (new UserRoleEntity())->setCode('ROLE_CUSTOMER_ADMIN');
        $customer1User = (new UserEntity())
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('Админ арендатора 1')
            ->setPassword('testingpassword')
            ->addRole($customer1UserRole)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer1)
            ->generateSalt();

        $customer2UserRole = (new UserRoleEntity())->setCode('ROLE_CUSTOMER_ADMIN');
        $customer2User = (new UserEntity())
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('Админ арендатора 2')
            ->setPassword('testingpassword')
            ->addRole($customer2UserRole)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer2)
            ->generateSalt();

        $customer3UserRole = (new UserRoleEntity())->setCode('ROLE_CUSTOMER_ADMIN');
        $customer3User = (new UserEntity())
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('Админ арендатора 2')
            ->setPassword('testingpassword')
            ->addRole($customer3UserRole)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer3)
            ->generateSalt();

        $manager->persist($superadmin);
        $manager->persist($customer1User);
        $manager->persist($customer2User);
        $manager->persist($customer3User);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CustomerFixture::class,
            ServiceFixture::class,
        ];
    }
}
