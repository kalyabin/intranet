<?php

namespace CustomerBundle\Tests\Form\Type;


use CustomerBundle\Entity\CustomerEntity;
use CustomerBundle\Form\Type\CustomerType;
use Tests\FormWebTestCase;

/**
 * Class CustomerTypeTest
 * @package CustomerBundle\Tests\Form\Type
 */
class CustomerTypeTest extends FormWebTestCase
{
    public function getFormClass()
    {
        return CustomerType::class;
    }

    public function getFormData()
    {
        return new CustomerEntity();
    }

    public function getValidData()
    {
        return [
            [
                'data' => [
                    'name' => 'testing',
                    'currentAgreement' => 'testing',
                ],
            ],
        ];
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => [
                    'name', 'currentAgreement',
                ]
            ],
        ];
    }
}
