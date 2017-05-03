<?php

namespace UserBunde\Tests\Form\Type;


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
    public function getValidData()
    {
        return [
            [
                'data' => [
                    'name' => 'testing',
                    'email' => 'testusertype@test.ru',
                    'password' => 'userpassword',
                    'userType' => UserEntity::TYPE_CUSTOMER,
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
                            'code' => 'CUSTOMER_ADMIN'
                        ],
                    ],
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
                            'code' => 'USER_CUSTOMER'
                        ],
                        [
                            'code' => 'FINANCE_CUSTOMER'
                        ]
                    ],
                ],
            ]
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

    public function getFormData()
    {
        return new UserEntity();
    }
}
