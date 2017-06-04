<?php

namespace TicketBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use TicketBundle\Entity\TicketCategoryEntity;
use TicketBundle\Entity\TicketEntity;
use UserBundle\Entity\UserEntity;

/**
 * Проверка прав доступа к тикету
 *
 * @package TicketBundle\Security
 */
class TicketVoter extends Voter
{
    /**
     * Право доступа - просмотр тикета
     */
    const VIEW = 'view';

    /**
     * Право доступа - постинг сообщений
     */
    const MESSAGE = 'message';

    /**
     * Право доступа - редактирование тикета
     */
    const UPDATE = 'update';

    /**
     * Право доступа - назначение менеджера
     */
    const ASSIGN = 'assign';

    /**
     * @var AccessDecisionManagerInterface
     */
    protected $decisionManager;

    /**
     * TicketVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * Проверка поддержки проверки права доступа данным классом
     *
     * @param string $attribute Право доступа
     * @param mixed $subject Объект доступа
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::MESSAGE, self::UPDATE, self::ASSIGN])) {
            return false;
        }

        if (!$subject instanceof TicketEntity) {
            return false;
        }

        return true;
    }

    /**
     * Проверка права доступа к тикету
     *
     * @param string $attribute Право доступа
     * @param TicketEntity $subject Тикет для доступа
     * @param TokenInterface $token Токен пользователя
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var UserEntity $user */
        $user = $token->getUser();
        /** @var TicketCategoryEntity $category */
        $category = $subject->getCategory();

        // проверка права доступа к категории
        if (!$this->decisionManager->decide($token, ['view'], $category)) {
            return false;
        }

        if ($user->getUserType() == UserEntity::TYPE_MANAGER) {
            // менеджер может делать с тикетом все что угодно, если у него есть право доступа
            // и если он не является автором данного тикета
            return !$subject->getCreatedBy() || $user->getId() != $subject->getCreatedBy()->getId();
        } elseif ($user->getUserType() == UserEntity::TYPE_CUSTOMER && $attribute == self::ASSIGN) {
            // арендатор не может назначать менеджеров на тикет
            return false;
        }

        // арендатор может делать с тикетом все что угодно, если он является своим
        if (!$subject->getCustomer()) {
            // если не указан контрагнт, то право доступа имеет автор тикета
            return $subject->getCreatedBy() ?
                $subject->getCreatedBy()->getId() == $user->getId() :
                false;
        } else if (!$user->getCustomer()) {
            // если вообще не указан контрагент у пользователя - значит вообще не имеет право доступа к тикету
            return false;
        }

        return $subject->getCustomer()->getId() == $user->getCustomer()->getId();
    }
}
