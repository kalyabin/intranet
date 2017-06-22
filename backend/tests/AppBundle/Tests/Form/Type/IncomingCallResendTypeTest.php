<?php

namespace AppBundle\Tests\Form\Type;


use AppBundle\Form\Type\IncomingCallResendType;
use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;


/**
 * Тестирование формы отправки звонка контрагенту
 *
 * @package AppBundle\Tests\Form\Type
 */
class IncomingCallResendTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class
        ])->getReferenceRepository();
    }

    protected function getFormClass()
    {
        return IncomingCallResendType::class;
    }

    protected function getFormData()
    {
        return new IncomingCallResendType();
    }

    public function getInvalidData()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        return [
            [
                'data' => [],
                'errorKeys' => ['customer', 'callerId'],
            ],
            [
                'data' => ['customer' => 'wrong identifier'],
                'errorKeys' => ['customer', 'callerId'],
            ],
            [
                'data' => ['customer' => $customer->getId()],
                'errorKeys' => ['callerId'],
            ]
        ];
    }

    public function getValidData()
    {
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        return [
            [
                'data' => [
                    'customer' => $customer->getId(),
                    'callerId' => '74951111111'
                ],
            ],
            [
                'data' => [
                    'customer' => $customer->getId(),
                    'callerId' => '74951111111',
                    'comment' => 'testing comment'
                ]
            ]
        ];
    }
}
