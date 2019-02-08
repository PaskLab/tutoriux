<?php

namespace App\Repository\Media;

use App\Library\BaseEntityRepository;

/**
 * Class LockRepository
 * @package MediaBundle\Entity
 */
class LockRepository extends BaseEntityRepository
{
    /**
     * @param array $mediaIds
     * @return array
     */
    public function locksCount(array $mediaIds)
    {
        $queryBuilder = $this->createQueryBuilder('l');
        return $queryBuilder
            ->select($queryBuilder->expr()->count('l'))
            ->where('l.media IN (:mediaIds) AND l.active = TRUE')
            ->setParameter('mediaIds', $mediaIds)
            ->getQuery()->getResult();
    }
}