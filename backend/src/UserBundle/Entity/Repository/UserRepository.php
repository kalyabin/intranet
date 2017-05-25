<?php

namespace UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use UserBundle\Entity\UserEntity;

/**
 * Класс-репозиторий для пользователей
 *
 * @package UserBundle\Entity\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * Получить общее количество элементов
     *
     * @return integer
     */
    public function getTotalCount(): int
    {
        $queryBuilder = $this->createQueryBuilder('u');

        return (int) $queryBuilder->select($queryBuilder->expr()->count('u.id'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Получить пользователя по идентификатору
     *
     * Возвращает null если пользователь не найден
     *
     * @param integer $id
     *
     * @return UserEntity|null
     */
    public function findOneById(int $id): ?UserEntity
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Получить пользователя по e-mail
     *
     * Возвращает null если пользователь не найден
     *
     * @param string $email
     *
     * @return UserEntity|null
     */
    public function findOneByEmail(string $email): ?UserEntity
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает true, если пользователь с указанным e-mail существует
     *
     * @param string $email E-mail пользователя для поиска
     * @param integer $excludedId ID пользователя, который надо исключать из поиска
     *
     * @return boolean
     */
    public function userIsExistsByEmail(?string $email, ?int $excludedId = null): bool
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        if (!is_null($excludedId)) {
            $query->andWhere('u.id <> :id')
                ->setParameter('id', $excludedId);
        }

        $count = (int) $query->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Получить всех пользователей с правом доступом role.
     *
     * @param string|string[] $role Право доступа или массив прав доступа
     *
     * @return IterableResult
     */
    public function findByRole($role): IterableResult
    {
        $role = is_array($role) ? $role : [$role];

        return $this->createQueryBuilder('u')
            ->distinct()
            ->join('u.role', 'r')
            ->where('r.code IN (:role)')
            ->setParameter('role', $role)
            ->groupBy('u.id')
            ->getQuery()
            ->iterate();
    }
}
