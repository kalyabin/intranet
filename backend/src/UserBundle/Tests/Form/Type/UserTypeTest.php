<?php

namespace UserBunde\Tests\Form\Type;

use CustomerBundle\Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\FormWebTestCase;
use UserBundle\Entity\UserEntity;
use UserBundle\Form\Type\UserType;
use UserBundle\Tests\DataFixtures\ORM\UserTestFixture;

/**
 * Тестирование формы создания или редактирования пользователя
 *
 * @package UserBunde\Tests\Form\Type
 */
class UserTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserTestFixture::class,
            CustomerTestFixture::class
        ])->getReferenceRepository();
    }

    protected function getFormData(): UserEntity
    {
        return $this->fixtures->getReference('active-user');
    }

    public function getValidData()
    {
        $user = $this->getFormData();

        return [
            [
                'data' => [
                    'name' => 'testing',
                    'status' => $user->getStatus(),
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'customer' => $user->getCustomer()->getId()
                ],
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'status' => $user->getStatus(),
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'CUSTOMER_ADMIN'
                        ],
                    ],
                    'customer' => $user->getCustomer()->getId()
                ],
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'status' => $user->getStatus(),
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'USER_CUSTOMER'
                        ],
                        [
                            'code' => 'FINANCE_CUSTOMER'
                        ]
                    ],
                    'customer' => $user->getCustomer()->getId()
                ],
            ],
        ];
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
            ],
            [
                'data' => [
                    'name' => 'testing',
                ]
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testing',
                ],
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                ]
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                ]
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => 'wrong user type',
                ]
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [],
                    ],
                ]
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'wrong role'
                        ],
                    ],
                ]
            ],
        ];
    }

    public function getFormClass()
    {
        return UserType::class;
    }
}
