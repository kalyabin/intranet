<?php

namespace Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;

/**
 * Модульное тестирование форм
 *
 * @package Tests
 */
abstract class FormWebTestCase extends WebTestCase
{
    /**
     * @var Form
     */
    protected $form;

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

        $container = static::$kernel->getContainer();

        $factory = $container->get('form.factory');

        $this->form = $factory->create($this->getFormClass(), $this->getFormData());
    }

    protected function tearDown()
    {
        $this->form = null;

        parent::tearDown();
    }

    /**
     * Тестирование формы на валидность
     *
     * @param array $data
     *
     * @dataProvider getValidData
     */
    public function testIsValid($data)
    {
        $this->form->submit($data);

        $this->assertChildrensHasKey($this->form, $data);

        if (!$this->form->isValid()) {
            echo $this->form->getErrors()->current()->getMessage();exit();
        }
//        $this->assertTrue($this->form->isValid());
    }

    /**
     * Тестирование формы на невалидность
     *
     * @param array $data
     * @param array $errorKeys Поля с ошибками
     *
     * @dataProvider getInvalidData
     */
    public function testIsInvalid($data, array $errorKeys = [])
    {
        $this->form->submit($data);

        $this->assertChildrensHasKey($this->form, $data);

        $this->assertFalse($this->form->isValid());

        if (!empty($errorKeys)) {
            foreach ($this->form as $child) {
                foreach ($child->getErrors(true) as $error) {
                    $originName = $error->getOrigin()->getName();
                    $childName = $child->getName();
                    $key = $childName != $originName ? $childName . '[' . $originName . ']' : $childName;
                    $this->assertContains($key, $errorKeys);
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
