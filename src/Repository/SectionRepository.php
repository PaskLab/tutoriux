<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use Exception;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\NodeRepositoryInterface;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;
use App\Library\BaseEntityRepository;

/**
 * Class SectionRepository
 * @package App\Repository
 */
class SectionRepository extends BaseEntityRepository implements NodeRepositoryInterface, TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository,
        TutoriuxORMBehaviors\Repository\MaterializedPathRepository;

    const NAVIGATION_LIFETIME = 86400;
    const LAST_UPDATE_LIFETIME = 86400;

    /**
     * Method used in conjunction with MaterializedPath Trait getTreeFrom***() methods.
     * Override MaterializedPath Trait addCriteria method.
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     * @throws Exception
     */
    public function addCriteria(QueryBuilder $queryBuilder): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st')
            ->leftJoin('s.sectionNavigations', 'sn')
            ->orderBy('sn.ordering')
            ->addOrderBy('s.ordering')
        ;

        if (!$this->isEditMode()) {
            $queryBuilder->innerJoin('s.translations', 'st')
                ->andWhere('st.active = true')
                ->andWhere('st.locale = :locale')
                ->setParameter('locale', $this->getLocale())
            ;
        }

        return $queryBuilder;
    }

    /**
     * @param array $excludedSectionIds
     * @return array
     * @throws Exception
     */
    public function findAllForTree($excludedSectionIds = array())
    {
        $queryBuilder = $this->getTreeFromQB(null, 's')
            ->select('s','st')
            ->innerjoin('s.mappings', 'm')
            ->leftJoin('s.sectionNavigations','sn')
            ->leftJoin('sn.navigation','n')
            ->groupBy('s.id')
            ->orderBy('s.ordering','ASC')
            ->addOrderBy('sn.ordering','ASC');

        if (!empty($excludedSectionIds)) {
            $queryBuilder
                ->andWhere('s.id NOT IN (:excludedSectionIds)')
                ->setParameter('excludedSectionIds',$excludedSectionIds);
        }

        if ($this->isEditMode()) {
            $queryBuilder->leftJoin('s.translations','st','WITH','st.locale = :locale')
                ->setParameter('locale',$this->getLocale());
        } else {
            $queryBuilder
                ->innerJoin('s.translations','st','WITH','st.locale = :locale AND st.active = true')
                ->setParameter('locale',$this->getLocale());
        }

        return $this->buildTree($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param $navigationId
     * @return mixed
     * @throws Exception
     */
    public function findByNavigation($navigationId)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st')
            ->innerJoin('s.sectionNavigations', 'sn')
            ->where('sn.navigation = :navigationId')
            ->orderBy('sn.ordering')

            ->setParameter('navigationId', $navigationId);

        if (!$this->isEditMode()) {
            $queryBuilder->innerJoin('s.translations', 'st')
                ->andWhere('st.active = true')
                ->andWhere('st.locale = :locale')
                ->setParameter('locale', $this->getLocale());
        }

        $result = $queryBuilder
            ->getQuery()
            ->setResultCacheLifetime(self::NAVIGATION_LIFETIME)
            ->useResultCache((!$this->isEditMode()))
            ->getResult();

        $this->buildTree(
            $this->getTreeFromQB($this->getNodeIds($result), 's')
                ->orderBy('s.ordering', 'ASC')
                ->addOrderBy('st.name', 'ASC')
                ->getQuery()
                ->setResultCacheLifetime(self::NAVIGATION_LIFETIME)
                ->useResultCache((!$this->isEditMode()))
                ->getResult()
        );

        return $result;
    }

    /**
     * CMS ONLY
     *
     * @return mixed
     */
    public function allWithJoinChildren()
    {
        $result = $this->createQueryBuilder('s')
            ->select('s')
            ->where('s.parent IS NULL')
            ->getQuery()
            ->setResultCacheLifetime(self::NAVIGATION_LIFETIME)
            ->useResultCache((!$this->isEditMode()))
            ->getResult();

        $queryBuilder = $this->getTreeFromQB($this->getNodeIds($result), 's', false);
        $result = $queryBuilder
            ->select('s', 'st', 'sm')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('s.mappings', 'sm', Expr\Join::WITH,
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('sm.type', ':type'),
                    $queryBuilder->expr()->eq('sm.context', ':context')
                )
            )

            ->orderBy('s.ordering','ASC')
            ->addOrderBy('st.name')
            ->setParameter('type', 'route')
            ->setParameter('context', 'cms')
            ->getQuery()
            ->setResultCacheLifetime(self::NAVIGATION_LIFETIME)
            ->useResultCache((!$this->isEditMode()))
            ->getResult();

        return $this->buildTree($result);
    }

    /**
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findRootsWithoutNavigation()
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st', 'c', 'ct')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('s.sectionNavigations', 'n')
            ->leftJoin('s.children', 'c')
            ->leftJoin('c.translations', 'ct')
            ->where('s.parent IS NULL')
            ->andWhere('n.id IS NULL')
            ->orderBy('s.ordering');

        return $this->processQuery($queryBuilder);
    }

    /**
     * Find Having Roles
     *
     * @param $roles
     *
     * @return mixed
     */
    public function findHavingRoles($roles)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s.id')
            ->innerJoin('s.roles', 'r')
            ->where('r.role IN (:roles)')
            ->setParameter('roles', $roles);

        // Array of array('id' => 1)
        $results = $queryBuilder->getQuery()->getScalarResult();

        $ids = array();
        foreach ($results as $section) {
            $ids[] = $section['id'];
        }

        // Return the list of IDs in a single array
        return $ids;
    }

    /**
     * @param null $queryBuilder
     * @return mixed|null
     */
    public function findLastUpdate($queryBuilder = null)
    {
        if (!$queryBuilder) {
            $queryBuilder = $this->createQueryBuilder('s');
        }

        try {
            return $queryBuilder->select('s.updatedAt')
                ->addOrderBy('s.updatedAt', 'DESC')
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
