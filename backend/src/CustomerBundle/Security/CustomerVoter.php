<?php

namespace CustomerBundle\Security;

use CustomerBundle\Entity\CustomerEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use UserBundle\Entity\UserEntity;

/**
 * Доступность пользователю для просмотра контрагента
 *
 * @package CustomerBundle\Security
 */
class CustomerVoter extends Voter
{
    /**
     * Просмотр контрагента
     */
    const VIEW = 'view';

    /**
     * Проверка поддержки данным классом права доступа
     *
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    public function supports($attribute, $subject): bool
    {
        if (!$subject instanceof CustomerEntity) {
            return false;
        }

        if (!in_array($attribute, [self::VIEW])) {
            return false;
        }

        return true;
    }

    /**
     * Проверка права доступа пользователю просмотра карточки контрагента
     *
     * @param string $attribute Тип доступа (по умолчанию - просмотр, view)
     * @param CustomerEntity $subject Объект для просмотра
     * @param TokenInterface $token Токен текущего пользвоателя
     *
     * @return bool
     */
    public function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var UserEntity $user */
        $user = $token->getUser();

        if ($user->getUserType() == UserEntity::TYPE_MANAGER && $attribute == self::VIEW) {
            // сотрудникам можно смотреть всех контрагентов
            return true;
        } else if ($user->getUserType() == UserEntity::TYPE_CUSTOMER && $attribute == self::VIEW) {
            // контрагентам можно смотреть только свои карточки
            return $user->getCustomer() && $user->getCustomer()->getId() == $subject->getId();
        }

        // во всех остальных случаях доступ не даём
        return false;
    }
}
