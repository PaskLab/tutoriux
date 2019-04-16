<?php

namespace App\Repository;

use App\Library\TranslatableRepositoryInterface;
use Doctrine\ORM\Query,
    Doctrine\ORM\QueryBuilder;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors,
    Tutoriux\DoctrineBehaviorsBundle\Model\Repository\NodeRepositoryInterface,
    App\Library\BaseEntityRepository;

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
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     * @throws \Exception
     */
    public function getCriteria(QueryBuilder $queryBuilder)
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @return QueryBuilder|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function allWithJoinChildren()
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st', 'sm', 'c', 'ct', 'cm')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('s.mappings', 'sm')
            ->leftJoin('s.children', 'c')
            ->leftJoin('c.translations', 'ct')
            ->leftJoin('c.mappings', 'cm')
            ->orderBy('s.ordering')
            ->addOrderBy('st.name');

        return $this->processQuery($queryBuilder);
    }

    /**
     * @return QueryBuilder|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * Find the last update of a Section entity
     *
     * @param null $queryBuilder
     * @return mixed
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
        } catch (\Exception $e) {
            return null;
        }
    }
}
