<?php

namespace UserBunde\Tests\Form\Type;

use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\FormWebTestCase;
use UserBundle\Entity\UserEntity;
use UserBundle\Form\Type\UserType;

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
            CustomerTestFixture::class
        ])->getReferenceRepository();
    }

    protected function getFormData(): UserEntity
    {
        return new UserEntity();
    }

    protected function getCustomer(): CustomerEntity
    {
        return $this->fixtures->getReference('none-customer');
    }

    public function getValidData()
    {
        $user = $this->getFormData();

        return [
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'customer' => $this->getCustomer()->getId()
                ],
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'ROLE_CUSTOMER_ADMIN'
                        ],
                    ],
                    'customer' => $this->getCustomer()->getId()
                ],
            ],
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'ROLE_USER_CUSTOMER'
                        ],
                        [
                            'code' => 'ROLE_FINANCE_CUSTOMER'
                        ]
                    ],
                    'customer' => $this->getCustomer()->getId()
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
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
                    'role' => [
                        [
                            'code' => 'ROLE_IT_CUSTOMER'
                        ],
                        [
                            'code' => 'ROLE_BOOKER_CUSTOMER'
                        ],
                    ],
                    'customer' => $this->getCustomer()->getId()
                ],
            ],
        ];
    }

    public function getFormClass()
    {
        return UserType::class;
    }
}
