<?php

namespace App\Repository;

use Doctrine\ORM\AbstractQuery;

use App\Library\BaseEntityRepository;

/**
 * Class UserNotificationRepository
 * @package App\Entity
 */
class UserNotificationRepository extends BaseEntityRepository
{
    /**
     * @param User $user
     * @return int
     */
    public function getNewNotificationCount(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('un');
        return $queryBuilder
            ->select($queryBuilder->expr()->count('un'))
            ->where('un.user = :userId')
            ->andWhere('un.viewed <> TRUE')
            ->setParameter('userId', $user->getId())
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getNotificationsForUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('un')
            ->select('un', 'n')
            ->innerJoin('un.notification', 'n')
            ->where('un.user = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('n.createdAt', 'DESC');

        return $this->processQuery($queryBuilder);
    }
}