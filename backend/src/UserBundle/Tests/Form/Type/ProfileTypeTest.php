<?php

namespace UserBunde\Tests\Form\Type;

use Tests\DataFixtures\ORM\CustomerTestFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\DataFixtures\ORM\UserTestFixture;
use Tests\FormWebTestCase;
use UserBundle\Form\Type\ProfileType;

/**
 * Тестирование формы изменения профиля
 *
 * @package UserBundle\Tests\Form\Type
 */
class ProfileTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    protected function setUp()
    {
        $this->fixtures = $this->loadFixtures([
            CustomerTestFixture::class,
            UserTestFixture::class,
        ])->getReferenceRepository();

        parent::setUp();
    }

    protected function getFormData()
    {
        return $this->fixtures->getReference('active-user');
    }

    protected function getFormClass()
    {
        return ProfileType::class;
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['name'],
            ]
        ];
    }

    public function getValidData()
    {
        return [
            [
                'data' => ['name' => 'New user name'],
            ]
        ];
    }
}
