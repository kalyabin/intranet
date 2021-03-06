<?php

namespace UserBunde\Tests\Form\Type;

use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\UserTestFixture;
use UserBundle\Entity\UserEntity;
use UserBundle\Form\Type\ChangeEmailType;

/**
 * Тестирование формы смены e-mail
 *
 * @package UserBundle\Tests\Form\Type
 */
class ChangeEmailTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    protected function setUp()
    {
        $this->fixtures = $this->loadFixtures([
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            UserTestFixture::class,
        ])->getReferenceRepository();

        parent::setUp();
    }

    protected function getFormData()
    {
        $data = new ChangeEmailType();
        /** @var UserEntity $user */
        $user = $this->fixtures->getReference('active-user');
        $data->setCurrentUserId($user->getId());
        return $data;
    }

    protected function getFormClass()
    {
        return ChangeEmailType::class;
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['newEmail'],
            ],
            [
                'data' => [
                    'newEmail' => 'wrong email',
                ],
                'errorKeys' => ['newEmail'],
            ],
            [
                'data' => [
                    // e-mail другого пользователя (тестируемый - testing@test.ru)
                    'newEmail' => 'inactive@test.ru',
                ],
                'errorKeys' => ['newEmail'],
            ]
        ];
    }

    public function getValidData()
    {
        return [
            [
                'data' => [
                    'newEmail' => 'newemail@test.ru',
                ],
            ],
            [
                'data' => [
                    'newEmail' => 'testing@test.ru',
                ]
            ]
        ];
    }
}
