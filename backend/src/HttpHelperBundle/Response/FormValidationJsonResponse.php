<?php

namespace HttpHelperBundle\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Абстрактный класс для JSON-ответов с валидацией форм
 *
 * @package HttpHelperBundle\Response
 */
class FormValidationJsonResponse extends JsonResponse
{
    /**
     * @var array JSON-ответ помимо ошибок валидации
     */
    public $jsonData = [];

    /**
     * @var string[] Ошибки валидации в виде key-value
     */
    protected $validationErrors = [];

    /**
     * Зафикировать ошибки валидации
     *
     * @param Form $form
     */
    public function handleForm(Form $form)
    {
        $this->jsonData['submitted'] = $form->isSubmitted();
        $this->jsonData['valid'] = $this->jsonData['submitted'] && $form->isValid();

        if (!$this->jsonData['valid']) {
            $errors = [];

            foreach ($form as $child) {
                foreach ($child->getErrors(true) as $error) {
                    $originName = $error->getOrigin()->getName();
                    $childName = $child->getName();
                    $key = $childName != $originName ? '[' . $childName . ']' . '[' . $originName . ']' : '[' . $childName . ']';
                    $errors[$form->getName() . $key] = $error->getMessage();
                }
            }

            $this->validationErrors = $errors;
            $this->setStatusCode(self::HTTP_BAD_REQUEST);
        }

        $this->setData(array_merge($this->jsonData, ['validationErrors' => $this->validationErrors]));
    }
}
