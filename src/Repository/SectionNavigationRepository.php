<?php

namespace App\Repository;

use Exception;
use App\Library\BaseEntityRepository;

/**
 * Class SectionNavigationRepository
 * @package App\Repository
 */
class SectionNavigationRepository extends BaseEntityRepository
{
    const LAST_UPDATE_LIFETIME = 86400;

    /**
     * @param null $queryBuilder
     * @return mixed|null
     */
    public function findLastUpdate($queryBuilder = null)
    {
        if (!$queryBuilder) {
            $queryBuilder = $this->createQueryBuilder('sn');
        }

        try {
            return $queryBuilder->select('sn.updatedAt')
                ->addOrderBy('sn.updatedAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->setResultCacheLifetime(self::LAST_UPDATE_LIFETIME)
                ->useResultCache(true)
                ->getSingleScalarResult();
        } catch (Exception $e) {
            return null;
        }
    }
}
