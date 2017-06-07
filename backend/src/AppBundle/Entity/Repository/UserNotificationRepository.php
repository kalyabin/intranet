<?php

namespace AppBundle\Entity\Repository;


use AppBundle\Entity\UserNotificationEntity;
use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\UserEntity;

/**
 * Репозиторий для получения уведомлений пользователя
 *
 * @package AppBundle\Entity\Repository
 */
class UserNotificationRepository extends EntityRepository
{
    /**
     * Получить все непрочтенные уведомления пользователя
     *
     * @param UserEntity $user
     *
     * @return UserNotificationEntity[]
     */
    public function findAllUnreadUserNotification(UserEntity $user): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.isRead = :isRead AND n.receiver = :receiver')
            ->setParameters([
                'isRead' => false,
                'receiver' => $user
            ])
            ->getQuery()
            ->getResult();
    }
}
