<?php

namespace RentBundle\Tests\Form\Type;


use Doctrine\Common\DataFixtures\ReferenceRepository;
use RentBundle\Entity\RoomEntity;
use RentBundle\Entity\RoomRequestEntity;
use RentBundle\Form\Type\RoomRequestType;
use Symfony\Component\Validator\Constraints\DateTime;
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
        $room = $this->fixtures->getReference('monday-only-room');

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
            [
                'data' => [
                    'room' => $room->getId(),
                    // во вторник недоступно
                    'from' => (new \DateTime())->setDate(2017, 7, 4)->setTime(0, 0)->format('Y-m-d H:i'),
                    'to' => (new \DateTime())->setDate(2017, 7, 4)->setTime(1, 0)->format('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    // в понедельник но в недоступное время
                    'from' => (new \DateTime())->setDate(2017, 7, 3)->setTime(0, 0)->format('Y-m-d H:i'),
                    'to' => (new \DateTime())->setDate(2017, 7, 3)->setTime(1, 0)->format('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    // в понедельник но в недоступное время
                    'from' => (new \DateTime())->setDate(2017, 7, 3)->setTime(16, 0)->format('Y-m-d H:i'),
                    'to' => (new \DateTime())->setDate(2017, 7, 3)->setTime(17, 0)->format('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    // в праздник недоступно
                    'from' => (new \DateTime())->add(new \DateInterval('P1D'))->setTIme(15, 0)->format('Y-m-d H:i'),
                    'to' => (new \DateTime())->add(new \DateInterval('P1D'))->setTime(16, 0)->format('Y-m-d H:i'),
                ],
                'errorKeys' => ['from', 'to'],
            ],
        ];
    }

    public function getValidData()
    {
        /** @var RoomEntity $room */
        $room = $this->fixtures->getReference('everyday-room');

        // в рабочий выходной доступно
        $from = (new \DateTime())->add(new \DateInterval('P2D'))->setTIme(10, 0)->format('Y-m-d H:i');
        $to = (new \DateTime())->add(new \DateInterval('P2D'))->setTIme(11, 0)->format('Y-m-d H:i');

        return [
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => $from,
                    'to' => $to,
                ]
            ],
            [
                'data' => [
                    'room' => $room->getId(),
                    'from' => $from,
                    'to' => $to,
                    'customerComment' => 'testing comment for customer'
                ]
            ],
        ];
    }
}
