<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use App\Library\BaseEntityRepository;

/**
 * Class NavigationRepository
 * @package App\Repository
 */
class NavigationRepository extends BaseEntityRepository
{
    const SECTION_BAR_ID = 1;
    const SECTION_MODULE_BAR_ID = 2;
    const GLOBAL_MODULE_BAR_ID = 3;
    const APP_MODULE_BAR_ID = 4;
    const NAVIGATION_LIFETIME = 86400;

    /**
     * @param null $appId
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findHaveSections($appId = null)
    {
        $query = $this->createQueryBuilder('n')
            ->select('n', 'sn', 's', 'st')
            ->innerJoin('n.sectionNavigations', 'sn')
            ->innerJoin('sn.section', 's')
            ->leftJoin('s.translations', 'st')
            ->orderBy('n.id', 'ASC')
            ->addOrderBy('sn.ordering', 'ASC');

        if ($appId) {
            $query->where('n.app = :appId');
            $query->setParameter('appId', $appId);
        }

        return $this->processQuery($query);
    }

    /**
     * @param string $code
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findOneByCode(string $code)
    {
        return $this->createQueryBuilder('n')
            ->where('n.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->setResultCacheLifetime(self::NAVIGATION_LIFETIME)
            ->useResultCache(true)
            ->getSingleResult();
    }
}
