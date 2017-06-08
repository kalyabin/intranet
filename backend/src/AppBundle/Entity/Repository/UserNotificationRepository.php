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
     * @param int $limit Количество сообщений для получения (по умолчанию - без лимита)
     *
     * @return UserNotificationEntity[]
     */
    public function findAllUnreadUserNotification(UserEntity $user, ?int $limit = null): array
    {
        $query = $this->createQueryBuilder('n')
            ->where('n.isRead = :isRead AND n.receiver = :receiver')
            ->setParameters([
                'isRead' => false,
                'receiver' => $user
            ])
            ->addOrderBy('n.createdAt', 'DESC')
            ->setFirstResult(0);

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }
}
