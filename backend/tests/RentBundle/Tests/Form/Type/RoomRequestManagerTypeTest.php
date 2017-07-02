<?php

namespace RentBundle\Tests\Form\Type;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestManagerType;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomRequestTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;

/**
 * Тестирование формы редактирования заявки
 *
 * @package RentBundle\Tests\Form\Type
 */
class RoomRequestManagerTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            RoomTestFixture::class,
            ServiceTestFixture::class,
            CustomerTestFixture::class,
            RoomRequestTestFixture::class,
        ])->getReferenceRepository();
    }

    public function getFormClass()
    {
        return RoomRequestManagerType::class;
    }

    public function getFormData(): RoomRequestEntity
    {
        /** @var RoomRequestEntity $data */
        $data = $this->fixtures->getReference('all-customer-everyday-room-request');
        return $data;
    }

    public function getInvalidData()
    {
        return [];
    }

    public function getValidData()
    {
        return [
            [
                'data' => [
                    'status' => RoomRequestEntity::STATUS_APPROVED,
                ]
            ],
            [
                'data' => [
                    'status' => RoomRequestEntity::STATUS_APPROVED,
                    'managerComment' => 'testing manager comment',
                ]
            ]
        ];
    }
}
