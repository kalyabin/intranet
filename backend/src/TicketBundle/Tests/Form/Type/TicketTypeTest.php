<?php

namespace TicketBundle\Tests\Form\Type;

use Tests\FormWebTestCase;
use TicketBundle\Form\Type\TicketType;

/**
 * Тестирование формы добавления заявки
 *
 * @package TicketBundle\Tests\Form\Type
 */
class TicketTypeTest extends FormWebTestCase
{
    /**
     * @inheritdoc
     */
    protected function getFormClass()
    {
        return TicketType::class;
    }

    /**
     * @inheritdoc
     */
    protected function getFormData()
    {
        return new TicketType();
    }

    /**
     * @inheritdoc
     */
    public function getInvalidData()
    {
        return [
            [
                'data' => [],
                'errorKeys' => ['text', 'title'],
            ],
            [
                'data' => [
                    'text' => null,
                    'title' => null,
                ],
                'errorKeys' => ['text', 'title'],
            ],
            [
                'data' => [
                    'text' => 'valid message text',
                ],
                'errorKeys' => ['title'],
            ],
            [
                'data' => [
                    'title' => 'valid message title',
                ],
                'errorKeys' => ['text'],
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
                    'text' => 'valid message text',
                    'title' => 'valid message title',
                ],
            ],
        ];
    }
}
