<?php

namespace TicketBundle\Security;

use CustomerBundle\Entity\Repository\ServiceRepository;
use CustomerBundle\Entity\ServiceActivatedEntity;
use CustomerBundle\Entity\ServiceEntity;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var ServiceRepository Репозиторий для получения дополнительных услуг
     */
    protected $serviceRepository;

    /**
     * TicketCategoryVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $entityManager)
    {
        $this->decisionManager = $decisionManager;
        $this->serviceRepository = $entityManager->getRepository(ServiceEntity::class);
    }

    /**
     * Проверить, что роль доступна только в соответствии с дополнительной услугой
     *
     * @param string $customerRole Роль арендатора
     *
     * @return bool
     */
    protected function isExtendedService($customerRole): bool
    {
        $services = $this->serviceRepository->findAll();
        foreach ($services as $service) {
            if ($service->getCustomerRole() == $customerRole) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::CREATE])) {
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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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
            // если данный тикет включен как отдельная услуга
            // и если по договору для контрагента эта услуга не доступна -
            // не даём пользоваться данной категорией при любом раскладе
            $customerRole = $subject->getCustomerRole();
            if ($this->isExtendedService($customerRole)) {
                $customer = $user->getCustomer();
                $allowedByService = false;
                foreach ($customer->getService() as $activatedService) {
                    /** @var ServiceActivatedEntity $activatedService */
                    if ($activatedService->getService()->getCustomerRole() == $customerRole) {
                        $allowedByService = true;
                        break;
                    }
                }
                if (!$allowedByService) {
                    return false;
                }
            }

            // проверка прав доступа
            if (empty($customerRole)) {
                // если не указана роль для арендаторов значит доступ имеют все арендаторы
                return true;
            } else {
                return $this->decisionManager->decide($token, [$subject->getCustomerRole()]);
            }
        }

        return false;
    }
}
