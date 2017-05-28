<?php

namespace TicketBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use TicketBundle\Entity\TicketCategoryEntity;
use UserBundle\Entity\UserEntity;

/**
 * Проверка прав доступа к управлению тикетов в категории
 *
 * @package TicketBundle\Security
 */
class TicketCategoryVoter extends Voter
{
    /**
     * Просмотр тикетов тикетов внутри категории
     */
    const VIEW = 'view';

    /**
     * Создание тикетов внутри категории
     */
    const CREATE = 'create';

    /**
     * @var AccessDecisionManagerInterface Доступ к текущему интерфейсу текущего пользователя
     */
    protected $decisionManager;

    /**
     * TicketCategoryVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::UPDATE])) {
            return false;
        }

        if (!$subject instanceof TicketCategoryEntity) {
            return false;
        }

        return true;
    }

    /**
     * Проверка прав доступа для текущего пользователя.
     *
     * Для менеджера проверяется поле managerRole в категории, для арендатора - customerRole.
     *
     * @param string $attribute Право доступа внутри категории (просмоттр, создание или редактирование)
     * @param TicketCategoryEntity $subject Категория
     * @param TokenInterface $token Токен текущего пользователя
     *
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): boolean
    {
        /** @var UserEntity $user */
        $user = $token->getUser();

        if ($attribute == self::CREATE && $user->getUserType() == UserEntity::TYPE_MANAGER) {
            // тикеты могут создавать только арендаторы
            return false;
        }

        if ($user->getUserType() == UserEntity::TYPE_MANAGER) {
            return $this->decisionManager->decide($token, [$subject->getManagerRole()]);
        } else if ($user->getUserType() == UserEntity::TYPE_CUSTOMER && $user->getCustomer()) {
            // проверка IT-аутсорсинга и SMART-бухгалтера
            // если по договору для контрагента эта услуга не доступна - не даём пользоваться данной категорией при любом раскладе
            if ($subject->getCustomerRole() == 'IT_CUSTOMER' && !$user->getCustomer()->getAllowItDepartment()) {
                return false;
            } else if ($subject->getCustomerRole() == 'BOOKER_CUSTOMER' && !$user->getCustomer()->getAllowBookerDepartment()) {
                return false;
            }

            // проверка прав доступа
            return $this->decisionManager->decide($token, [$subject->getCustomerRole()]);
        }

        return false;
    }
}
