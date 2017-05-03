<?php

namespace UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UserBundle\Entity\UserEntity;
use UserBundle\Utils\RolesManager;

/**
 * Валидатор ролей
 *
 * @package UserBundle\Validator\Constraints
 */
class UserRoleValidator extends ConstraintValidator
{
    /**
     * @var RolesManager
     */
    protected $rolesManager;

    /**
     * UserRoleValidator constructor.
     *
     * @param RolesManager $rolesManager
     */
    public function __construct(RolesManager $rolesManager)
    {
        $this->rolesManager = $rolesManager;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var UserRole $constraint */
        // проверить тип роли
        $availRoles = $this->rolesManager->getRolesLables();
        $availRoles = array_keys($availRoles);
        if (!in_array($value, $availRoles)) {
            $this->context->addViolation($constraint->message, [
                '%string%' => $value,
            ]);
            return;
        }

        $entity = $this->context->getObject();

        // проверить роль по типу пользователя
        $userType = null;

        if (is_string($constraint->userTypeCallback) && method_exists($entity, $constraint->userTypeCallback)) {
            $userType = $entity->{$constraint->userTypeCallback}();
        } else if (is_callable($constraint->userTypeCallback)) {
            $userType = call_user_func($constraint->userTypeCallback);
        }

        if ($userType) {
            $availRoles = $this->rolesManager->getRolesByUserType();
            $availRoles = isset($availRoles[$userType]) ? $availRoles[$userType] : [];
            if (!in_array($value, $availRoles)) {
                $this->context->addViolation($constraint->message, [
                    '%string%' => $value,
                ]);
                return;
            }
        }
    }
}
