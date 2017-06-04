<?php

namespace TicketBundle\Tests\Form\Type;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Tests\FormWebTestCase;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Form\Type\TicketType;
use TicketBundle\Tests\DataFixtures\ORM\TicketCategoryTestFixture;

/**
 * Тестирование формы добавления заявки
 *
 * @package TicketBundle\Tests\Form\Type
 */
class TicketTypeTest extends FormWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([TicketCategoryTestFixture::class])->getReferenceRepository();
    }

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
                'errorKeys' => ['category', 'text', 'title'],
            ],
            [
                'data' => [
                    'category' => null,
                    'text' => null,
                    'title' => null,
                ],
                'errorKeys' => ['category', 'text', 'title'],
            ],
            [
                'data' => [
                    'category' => null,
                    'text' => 'valid message text',
                ],
                'errorKeys' => ['category', 'title'],
            ],
            [
                'data' => [
                    'category' => null,
                    'title' => 'valid message title',
                ],
                'errorKeys' => ['category', 'text'],
            ],
            [
                'data' => [
                    'text' => 'valid message text',
                    'title' => 'valid message title',
                ],
                'errorKeys' => ['category'],
            ],
            [
                'data' => [
                    'category' => 'invalid_category',
                    'text' => 'valid message text',
                    'title' => 'valid message title',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getValidData()
    {
        /** @var TicketCategoryEntity $category */
        $category = $this->fixtures->getReference('it-department');

        return [
            [
                'data' => [
                    'category' => $category->getId(),
                    'text' => 'valid message text',
                    'title' => 'valid message title',
                ],
            ],
        ];
    }
}
