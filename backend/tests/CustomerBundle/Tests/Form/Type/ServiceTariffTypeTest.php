<?php

namespace CustomerBundle\Tests\Form\Type;

use CustomerBundle\Entity\ServiceTariffEntity;
use CustomerBundle\Form\Type\ServiceTariffType;
use Tests\FormWebTestCase;

/**
 * Тестирование формы заведения или редактирования тарифа
 *
 * @package CustomerBundle\Tests\Form\Type
 */
class ServiceTariffTypeTest extends FormWebTestCase
{
    public function getFormClass()
    {
        return ServiceTariffType::class;
    }

    public function getFormData()
    {
        return new ServiceTariffEntity();
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['title', 'isActive', 'monthlyCost']
            ],
            [
                'data' => [
                    'title' => 'testing title',
                ],
                'errorKeys' => ['isActive', 'monthlyCost'],
            ],
            [
                'data' => [
                    'title' => 'testing title',
                    'isActive' => 'invalid boolean'
                ],
                'errorKeys' => ['isActive', 'monthlyCost'],
            ],
        ];
    }

    public function getValidData()
    {
        return [
            [
                'data' => [
                    'title' => 'testing title',
                    'isActive' => true,
                    'monthlyCost' => 100
                ]
            ],
            [
                'data' => [
                    'title' => 'testing title',
                    'isActive' => false,
                    'monthlyCost' => 500.32
                ]
            ],
        ];
    }
}
