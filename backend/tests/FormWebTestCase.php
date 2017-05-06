<?php

namespace Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;

/**
 * Модульное тестирование форм
 *
 * @package Tests
 */
abstract class FormWebTestCase extends WebTestCase
{
    /**
     * @var FormFactory
     */
    protected $factory;

    /**
     * Получить валидные данные для формы
     *
     * @return array
     */
    abstract public function getValidData();

    /**
     * Получить невалидные данные для формы
     *
     * @return array
     */
    abstract public function getInvalidData();

    /**
     * Получить класс для формы
     *
     * @return string
     */
    abstract protected function getFormClass();

    /**
     * Получить данные для формы по умолчанию
     *
     * @return mixed
     */
    abstract protected function getFormData();

    protected function setUp()
    {
        static::bootKernel();

        $container = $this->getContainer();

        $this->factory = $container->get('form.factory');
    }

    /**
     * Тестирование формы на валидность
     */
    public function testIsValid()
    {
        foreach ($this->getValidData() as $item) {
            $data = $item['data'];
            /** @var Form $form */
            $form = $this->factory->create($this->getFormClass(), $this->getFormData());

            $form->submit($data);

            $errorMessage = '';

            if (!$form->isValid()) {
                $errorMessage = $form->getErrors(true)->current()->getOrigin()->getName() . ': ' .
                    $form->getErrors(true)->current()->getMessage();
            }

            $this->assertEmpty($errorMessage, $errorMessage);
        }
    }

    /**
     * Тестирование формы на невалидность
     */
    public function testIsInvalid()
    {
        foreach ($this->getInvalidData() as $item) {
            $data = $item['data'];
            $errorKeys = !empty($item['errorKeys']) ? $item['errorKeys'] : [];

            $form = $this->factory->create($this->getFormClass(), $this->getFormData());

            $form->submit($data);

            $this->assertChildrensHasKey($form, $data);

            $this->assertFalse($form->isValid());

            if (!empty($errorKeys)) {
                foreach ($form as $child) {
                    foreach ($child->getErrors(true) as $error) {
                        $originName = $error->getOrigin()->getName();
                        $childName = $child->getName();
                        $key = $childName != $originName ? $childName . '[' . $originName . ']' : $childName;
                        $this->assertContains($key, $errorKeys);
                    }
                }
            }
        }
    }

    /**
     * Проверка всех переданных данных и наличие их в форме
     *
     * @param Form  $form
     * @param array $data
     */
    protected function assertChildrensHasKey(Form $form, array $data)
    {
        $view = $form->createView();

        $children = $view->children;

        foreach ($data as $key => $value) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
