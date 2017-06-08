<?php

namespace Tests\DataFixtures\ORM;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use UserBundle\Entity\UserCheckerEntity;
use UserBundle\Entity\UserEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\UserRoleEntity;
use UserBundle\Utils\UserManager;

/**
 * Фикстуры пользователей для тестирования
 *
 * @package UserBundle\Tests\DataFixtures\ORM
 */
class UserTestFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userService */
        $userService = $this->container->get('user.manager');

        // пользователи типа "арендатор" должны быть привязаны к контрагенту
        /** @var CustomerEntity $customer */
        $customer = $this->getReference('all-customer');

        // создать активного пользователя
        $customerRole = new UserRoleEntity();
        $customerRole->setCode('ROLE_CUSTOMER_ADMIN');
        $activeUser = new UserEntity();
        $activeUser
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('Testing user')
            ->setEmail('testing@test.ru')
            ->setPassword('testpassword')
            ->addRole($customerRole)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer)
            ->generateSalt();
        $userService->encodeUserPassword($activeUser, $activeUser->getPassword());

        // создать неактивного пользователя с кодом подтверждения
        $customerRole = new UserRoleEntity();
        $customerRole->setCode('ROLE_CUSTOMER_ADMIN');
        $inactiveUser = new UserEntity();
        $inactiveUser
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_NEED_ACTIVATION)
            ->setName('Need activation user')
            ->setEmail('inactive@test.ru')
            ->addRole($customerRole)
            ->setPassword('testpassword')
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer)
            ->generateSalt();
        $userService->encodeUserPassword($inactiveUser, $inactiveUser->getPassword());
        $checker = new UserCheckerEntity();
        $checker
            ->setType(UserCheckerEntity::TYPE_ACTIVATION_CODE)
            ->setAttempts(0)
            ->setUser($inactiveUser)
            ->generateCode();
        $inactiveUser->addChecker($checker);

        // создать заблокированного пользователя
        $customerRole = new UserRoleEntity();
        $customerRole->setCode('ROLE_CUSTOMER_ADMIN');
        $lockedUser = new UserEntity();
        $lockedUser
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_LOCKED)
            ->setName('Locked user')
            ->addRole($customerRole)
            ->setEmail('locked@test.ru')
            ->setPassword('testpassword')
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->setCustomer($customer)
            ->generateSalt();
        $userService->encodeUserPassword($lockedUser, $lockedUser->getPassword());

        // создать супер-админа
        $superadmin = new UserEntity();
        $superadmin
            ->setCreatedAt(new \DateTime())
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setName('superadmin')
            ->setEmail('superadmin@test.ru')
            ->setPassword('testpassword')
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->generateSalt();
        $role = new UserRoleEntity();
        $role->setCode('ROLE_SUPERADMIN');
        $superadmin->addRole($role);
        $userService->encodeUserPassword($superadmin, $superadmin->getPassword());

        // создать пользователя-менеджера тикетной системы только для другой категории
        $role = new UserRoleEntity();
        $role->setCode('ROLE_IT_MANAGEMENT');
        $itManager = new UserEntity();
        $itManager
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket manager second')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager-other@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        // создать пользователя-менеджера тикетной системы без доступа к тикетной системе
        $role = new UserRoleEntity();
        $role->setCode('ROLE_DOCUMENT_MANAGEMENT');
        $documentManager = new UserEntity();
        $documentManager
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket manager second')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_MANAGER)
            ->addRole($role)
            ->setEmail('ticket-manager-denied@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        // другой пользователь тикетной системы от другого контрагента
        $role = new UserRoleEntity();
        $role->setCode('ROLE_CUSTOMER_ADMIN');
        /** @var CustomerEntity $customer */
        $customerNone = $this->getReference('none-customer');
        $noneCustomerUser = new UserEntity();
        $noneCustomerUser
            ->setCreatedAt(new \DateTime())
            ->setName('testing ticket customer')
            ->setStatus(UserEntity::STATUS_ACTIVE)
            ->setUserType(UserEntity::TYPE_CUSTOMER)
            ->addRole($role)
            ->setCustomer($customerNone)
            ->setEmail('ticket-customer+other@test.ru')
            ->setPassword('testingpassword')
            ->generateSalt();

        $manager->persist($noneCustomerUser);
        $manager->persist($documentManager);
        $manager->persist($itManager);
        $manager->persist($lockedUser);
        $manager->persist($activeUser);
        $manager->persist($inactiveUser);
        $manager->persist($superadmin);
        $manager->flush();

        $this->addReference('none-customer-user', $noneCustomerUser);
        $this->addReference('document-manager-user', $documentManager);
        $this->addReference('it-manager-user', $itManager);
        $this->addReference('user-customer', $customerNone);
        $this->addReference('locked-user', $lockedUser);
        $this->addReference('inactive-user', $inactiveUser);
        $this->addReference('active-user', $activeUser);
        $this->addReference('superadmin-user', $superadmin);
    }

    /**
     * От пользователей зависит вся система кроме контрагентов
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
