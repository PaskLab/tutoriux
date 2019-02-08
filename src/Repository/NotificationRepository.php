<?php

namespace App\Repository;

use App\Library\BaseEntityRepository;

/**
 * Class NotificationRepository
 * @package App\Entity
 */
class NotificationRepository extends BaseEntityRepository
{
    /**
     * @param User $user
     * @return mixed
     */
    public function getNotificationsForUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('n')
            ->select('n')
            ->innerJoin('n.users', 'un')
            ->where('un.user = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('n.createdAt', 'DESC');

        return $this->processQuery($queryBuilder);
    }
}