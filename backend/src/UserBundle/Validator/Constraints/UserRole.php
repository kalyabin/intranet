<?php

namespace UserBundle\Validator\Constraints;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * Валидация ролей
 *
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @package UserBundle\Validator\Constraints
 */
class UserRole extends Constraint
{
    /**
     * @var string|callable Callback-функция для получения типа пользователя
     */
    public $userTypeCallback;

    /**
     * @var string Текст об ошибке
     */
    public $message;

    /**
     * Валидатор
     *
     * @return string
     */
    public function validatedBy()
    {
        return UserRoleValidator::class;
    }
}
