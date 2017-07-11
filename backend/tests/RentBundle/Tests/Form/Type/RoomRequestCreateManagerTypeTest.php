<?php

namespace RentBundle\Tests\Form\Type;

use CustomerBundle\Entity\CustomerEntity;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestCreateManagerType;
use Tests\DataFixtures\ORM\CustomerTestFixture;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\DataFixtures\ORM\ServiceTestFixture;
use Tests\FormWebTestCase;

/**
 * Тестирование формы создания заявки от менеджера
 *
 * @package RentBundle\Tests\Form\Type
 */
class RoomRequestCreateManagerTypeTest extends FormWebTestCase
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
            CustomerTestFixture::class
        ])->getReferenceRepository();
    }

    public function getFormData()
    {
        return new RoomRequestEntity();
    }

    public function getFormClass()
    {
        return RoomRequestCreateManagerType::class;
    }

    public function getInvalidData()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        return [
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i', time() + 86400 + 86400),
                    'customerComment' => 'testing comment for customer'
                ],
                'errorKeys' => [
                    'customer',
                ]
            ],
        ];
    }

    public function getValidData()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');
        /** @var CustomerEntity $customer */
        $customer = $this->fixtures->getReference('all-customer');

        return [
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i', time() + 86400 + 86400),
                    'customerComment' => 'testing comment for customer',
                    'customer' => $customer->getId()
                ],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i', time() + 86400 + 86400),
                    'customerComment' => 'testing comment for customer',
                    'managerComment' => 'testing comment for manager',
                    'customer' => $customer->getId()
                ],
            ],
        ];
    }
}
