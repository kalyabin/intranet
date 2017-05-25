<?php

namespace UserBunde\Tests\Utils;


use Liip\FunctionalTestBundle\Test\WebTestCase;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Тестирование класса RolesManager
 *
 * @package UserBunde\Tests\Utils
 */
class RolesManagerTest extends WebTestCase
{
    /**
     * @var RolesManager
     */
    protected $rolesManager;

    protected function setUp()
    {
        parent::setUp();

        $container = $this->getContainer();

        $this->rolesManager = $container->get('user.roles_manager');
    }

    /**
     * @covers RolesManager::getRolesByUserType()
     */
    public function testRolesByUserType()
    {
        $userTypes = [UserEntity::TYPE_CUSTOMER, UserEntity::TYPE_MANAGER];

        $expectedRoles = $this->rolesManager->getRolesByUserType();

        $this->assertEquals(count($userTypes), count($expectedRoles));

        foreach ($userTypes as $userType) {
            $this->assertArrayHasKey($userType, $expectedRoles);
            $this->assertNotEmpty($expectedRoles[$userType]);
            $this->assertContainsOnly('string', $expectedRoles[$userType]);
        }
    }

    /**
     * @covers RolesManager::getRolesLables()
     * @depends testRolesByUserType
     */
    public function testGetRolesLabels()
    {
        $roles = [];

        foreach ($this->rolesManager->getRolesByUserType() as $userType) {
            $roles = array_merge($roles, $userType);
            $roles = array_unique($roles);
        }

        $rolesLabels = $this->rolesManager->getRolesLables();
        $this->assertNotEmpty($rolesLabels);
        $this->assertEquals(count($roles), count($rolesLabels));

        foreach ($roles as $role) {
            $this->assertArrayHasKey($role, $rolesLabels);
            $this->assertInternalType('string', $rolesLabels[$role]);
        }
    }

    /**
     * @covers RolesManager::getChildRoles()
     */
    public function testGetChildRoles()
    {
        // по коду USER_MANAGEMENT должны получить USER_MANAGEMENT и SUPERADMIN, т.к. SUPERADMIN обладает ролью USER_MANAGEMENT
        $result = $this->rolesManager->getParentRoles('USER_MANAGEMENT');

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArraySubset([
            'USER_MANAGEMENT',
            'SUPERADMIN'
        ], $result);

        // по коду USER_MANAGEMENT и IT_MANAGEMENT должны получить SUPERADMIN, USER_MANAGEMENT, IT_MANAGEMENT
        $result = $this->rolesManager->getParentRoles(['USER_MANAGEMENT', 'IT_MANAGEMENT']);

        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertArraySubset([
            'USER_MANAGEMENT',
            'SUPERADMIN',
            'IT_MANAGEMENT',
        ], $result);

        // по коду FINANCE_CUSTOMER - FINANCE_CUSTOMER и CUSTOMER_ADMIN
        $result = $this->rolesManager->getParentRoles(['FINANCE_CUSTOMER']);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArraySubset([
            'FINANCE_CUSTOMER',
            'CUSTOMER_ADMIN',
        ], $result);
    }
}
