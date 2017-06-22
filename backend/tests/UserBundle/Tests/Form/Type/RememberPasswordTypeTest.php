<?php

namespace UserBunde\Tests\Form\Type;

use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Form\Type\RememberPasswordType;

/**
 * Тестирование формы напоминания пароля
 *
 * @package UserBundle\Tests\Form\Type
 */
class RememberPasswordTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    protected function setUp()
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getFormClass()
    {
        return RememberPasswordType::class;
    }

    /**
     * @inheritdoc
     */
    protected function getFormData()
    {
        return new RememberPasswordType();
    }

    /**
     * @inheritdoc
     */
    public function getValidData()
    {
        return [
            [
                'data' => [
                    'email' => 'testing@test.ru',
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['email'],
            ],
            [
                'data' => [
                    'email' => 'wrong email format',
                ],
                'errorKeys' => ['email'],
            ],
            [
                'data' => [
                    'email' => 'non-existent@test.ru',
                ],
                'errorKeys' => ['email'],
            ],
        ];
    }
}
