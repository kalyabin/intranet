<?php

namespace CustomerBundle\Tests\Form\Type;

use CustomerBundle\Entity\ServiceEntity;
use CustomerBundle\Form\Type\ServiceType;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;

/**
 * Тестирование формы добавления и редактирования услуги
 *
 * @package CustomerBundle\Tests\Form\Type
 */
class ServiceTypeTest extends FormWebTestCase
{
    public function getFormClass()
    {
        return ServiceType::class;
    }

    public function getFormData()
    {
        return new ServiceEntity();
    }

    public function getValidData()
    {
        $this->loadFixtures([ServiceTestFixture::class]);

        return [
            [
                'data' => [
                    'id' => 'test-department',
                    'isActive' => true,
                    'title' => 'Test department',
                    'customerRole' => 'ROLE_MAINTAINCE_CUSTOMER',
                    'tariff' => [
                        [
                            'title' => 'new tariff plan',
                            'isActive' => true,
                            'monthlyCost' => 100.5
                        ],
                    ],
                ],
            ],
            [
                'data' => [
                    'id' => 'test-department',
                    'description' => 'testing description',
                    'isActive' => false,
                    'title' => 'Test department',
                    'customerRole' => 'ROLE_MAINTAINCE_CUSTOMER',
                ]
            ],
        ];
    }

    public function getInvalidData()
    {
        $fixtures = $this->loadFixtures([ServiceTestFixture::class])->getReferenceRepository();
        /** @var ServiceEntity $existsService */
        $existsService = $fixtures->getReference('service-it');

        return [
            [
                'data' => [],
                'errorKeys' => [
                    'id', 'isActive', 'title', 'customerRole',
                ]
            ],
            [
                'data' => [
                    'id' => $existsService->getId(),
                    'isActive' => 'wrong boolean type',
                    'title' => '',
                    'customerRole' => 'NON EXISTENT ROLE'
                ],
                'errorKeys' => [
                    'id', 'isActive', 'title', 'customerRole',
                ]
            ],
            [
                'data' => [
                    'id' => 'test-department',
                    'isActive' => false,
                    'title' => 'testing department',
                    'description' => 'text type',
                    'tariff' => [
                        [],
                    ],
                ],
                'errorKeys' => [
                    'customerRole', 'tariff[title]', 'tariff[monthlyCost]', 'tariff[isActive]',
                ]
            ],
            [
                'data' => [
                    'id' => 'test-department',
                    'isActive' => true,
                    'title' => 'testing department',
                    'description' => 'text type',
                    'customerRole' => 'ROLE_IT_MANAGEMENT',
                ],
                'errorKeys' => [
                    'customerRole',
                ]
            ],
        ];
    }
}
