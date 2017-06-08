<?php

namespace TicketBundle\Tests\Form\Type;

use Tests\FormWebTestCase;
use TicketBundle\Form\Type\TicketMessageType;

/**
 * Тестирование формы добавления сообщения
 *
 * @package TicketBundle\Tests\Form\Type
 */
class TicketMessageTypeTest extends FormWebTestCase
{
    /**
     * @inheritdoc
     */
    protected function getFormClass()
    {
        return TicketMessageType::class;
    }

    /**
     * @inheritdoc
     */
    protected function getFormData()
    {
        return new TicketMessageType();
    }

    /**
     * @inheritdoc
     */
    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['text']
            ],
            [
                'data' => [
                    'text' => null,
                ],
                'errorKeys' => ['text'],
            ],
            [
                'data' => [
                    'text' => '',
                ],
                'errorKeys' => ['text']
            ],
            [
                'data' => [
                    'text' => 'txt',
                    'closeTicket' => [],
                ],
                'errorKeys' => ['closeTicket'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getValidData()
    {
        return [
            [
                'data' => [
                    'text' => 'valid text data',
                ],
            ],
            [
                'data' => [
                    'text' => 'valid text data',
                    'closeTicket' => '1',
                ],
            ],
            [
                'data' => [
                    'text' => 'valid text data',
                    'closeTicket' => '0',
                ],
            ],
            [
                'data' => [
                    'text' => 'valid text data',
                    'closeTicket' => true,
                ],
            ],
            [
                'data' => [
                    'text' => 'valid text data',
                    'closeTicket' => false,
                ],
            ],
        ];
    }
}
