<?php

namespace RentBundle\Tests\Form\Type;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestType;
use Tests\DataFixtures\ORM\RoomTestFixture;
use Tests\FormWebTestCase;

/**
 * Тестирование формы заявки на аренду помещения
 *
 * @package RentBundle\Tests\Form\Type
 */
class RoomRequestTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([RoomTestFixture::class])->getReferenceRepository();
    }

    public function getFormClass()
    {
        return RoomRequestType::class;
    }

    public function getFormData()
    {
        return new RoomRequestEntity();
    }

    public function getInvalidData()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        return [
            [
                'data' => [],
                'errorKeys' => ['room', 'from', 'to'],
            ],
            [
                'data' => [
                    'room' => -100,
                    'from' => 'wrong date format',
                    'to' => 'wrong date format',
                ],
                'errorKeys' => ['room', 'from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('d.m.Y H:i'),
                    'to' => date('d.m.Y H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i'),
                    'to' => date('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
        ];
    }

    public function getValidData()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        return [
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i', time() + 86400 + 86400),
                ]
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => date('Y-m-d H:i', time() + 86400),
                    'to' => date('Y-m-d H:i', time() + 86400 + 86400),
                    'customerComment' => 'testing comment for customer'
                ]
            ],
        ];
    }
}
