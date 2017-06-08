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
        // по коду ROLE_USER_MANAGEMENT должны получить ROLE_USER_MANAGEMENT и ROLE_SUPERADMIN, т.к. ROLE_SUPERADMIN обладает ролью ROLE_USER_MANAGEMENT
        $result = $this->rolesManager->getParentRoles('ROLE_USER_MANAGEMENT');

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArraySubset([
            'ROLE_USER_MANAGEMENT',
            'ROLE_SUPERADMIN'
        ], $result);

        // по коду ROLE_USER_MANAGEMENT и ROLE_IT_MANAGEMENT должны получить ROLE_SUPERADMIN, ROLE_USER_MANAGEMENT, ROLE_IT_MANAGEMENT
        $result = $this->rolesManager->getParentRoles(['ROLE_USER_MANAGEMENT', 'ROLE_IT_MANAGEMENT']);

        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertArraySubset([
            'ROLE_USER_MANAGEMENT',
            'ROLE_SUPERADMIN',
            'ROLE_IT_MANAGEMENT',
        ], $result);

        // по коду ROLE_FINANCE_CUSTOMER - ROLE_FINANCE_CUSTOMER и ROLE_CUSTOMER_ADMIN
        $result = $this->rolesManager->getParentRoles(['ROLE_FINANCE_CUSTOMER']);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArraySubset([
            'ROLE_FINANCE_CUSTOMER',
            'ROLE_CUSTOMER_ADMIN',
        ], $result);
    }
}
