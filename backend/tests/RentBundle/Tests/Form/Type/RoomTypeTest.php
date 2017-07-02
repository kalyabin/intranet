<?php

namespace RentBundle\Tests\Form\Type;


use RentBundle\Entity\RoomEntity;
use RentBundle\Form\Type\RoomType;
use Tests\FormWebTestCase;

/**
 * Тестирование формы добавления или дерактирования помещения
 *
 * @package RentBundle\Tests\Form\Type
 */
class RoomTypeTest extends FormWebTestCase
{
    public function getFormClass()
    {
        return RoomType::class;
    }

    public function getFormData()
    {
        return new RoomEntity();
    }

    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => [
                    'type', 'title', 'address', 'hourlyCost',
                ]
            ],
            [
                'data' => [
                    'type' => 'wrong type',
                ],
                'errorKeys' => [
                    'type', 'title', 'address', 'hourlyCost',
                ],
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_CONFERENCE,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 100.5,
                    'schedule' => ['wrong schedule'],
                    'scheduleBreak' => ['wrong schedule break'],
                    'holidays' => ['wrong holiday item'],
                ],
                'errorKeys' => [
                    'schedule', 'scheduleBreak', 'holidays',
                ],
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_CONFERENCE,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 100.5,
                    'schedule' => [
                        [
                            'weekday' => -1,
                            'schedule' => [
                                [
                                    'from' => '09:00',
                                    'to' => '18:00'
                                ]
                            ]
                        ]
                    ],
                    'scheduleBreak' => [
                        [
                            'wrong format'
                        ]
                    ],
                    'holidays' => ['01.01.2017'],
                ],
                'errorKeys' => [
                    'schedule', 'scheduleBreak', 'holidays',
                ],
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_MEETING,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 500,
                    'schedule' => [
                        [
                            'weekday' => 8,
                            'schedule' => [
                                [
                                    'from' => '09:00',
                                    'to' => '18:00'
                                ]
                            ]
                        ]
                    ],
                    'scheduleBreak' => [
                        [
                            'from' => 'wrong format',
                            'to' => 'wrong format',
                        ]
                    ],
                    'holidays' => ['2017-01-01'],
                ],
                'errorKeys' => [
                    'schedule', 'scheduleBreak',
                ],
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_MEETING,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 500,
                    'schedule' => [
                        [
                            'weekday' => 5,
                            'schedule' => [
                                [
                                    'from' => 'wrong format',
                                    'to' => 'wrong format'
                                ]
                            ]
                        ]
                    ],
                    'scheduleBreak' => [
                        [
                            'from' => '09:00',
                            'to' => '18:00',
                        ]
                    ],
                    'holidays' => ['2017-01-01'],
                ],
                'errorKeys' => [
                    'schedule',
                ],
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_MEETING,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 500,
                    'schedule' => [
                        [
                            'weekday' => 5,
                            'schedule' => [
                                [
                                    'from' => '18:00',
                                    'to' => '09:00'
                                ]
                            ]
                        ]
                    ],
                    'scheduleBreak' => [
                        [
                            'from' => '15:00',
                            'to' => '09:00',
                        ]
                    ],
                    'holidays' => ['2017-01-01'],
                ],
                'errorKeys' => [
                    'schedule', 'scheduleBreak'
                ],
            ],
        ];
    }

    public function getValidData()
    {
        return [
            [
                'data' => [
                    'type' => RoomEntity::TYPE_MEETING,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 500,
                ]
            ],
            [
                'data' => [
                    'type' => RoomEntity::TYPE_MEETING,
                    'title' => 'testing title',
                    'address' => 'testing address',
                    'hourlyCost' => 100500,
                    'schedule' => [
                        [
                            'weekday' => 1,
                            'schedule' => [
                                [
                                    'from' => '09:00',
                                    'to' => '19:00'
                                ]
                            ]
                        ],
                        [
                            'weekday' => 2,
                            'schedule' => [
                                [
                                    'from' => '10:00',
                                    'to' => '20:00'
                                ]
                            ]
                        ]
                    ],
                    'scheduleBreak' => [
                        [
                            'from' => '12:00',
                            'to' => '13:00',
                        ],
                        [
                            'from' => '17:00',
                            'to' => '18:00',
                        ]
                    ],
                    'holidays' => ['2017-01-01', '2017-01-13'],
                    'requestPause' => 20
                ]
            ]
        ];
    }
}
