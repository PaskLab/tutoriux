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

    /**
     * Extend trait getCriteria method
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function getCriteria(QueryBuilder $queryBuilder)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st')
            ->leftJoin('s.sectionNavigations', 'sn')
            ->orderBy('sn.ordering')
            ->addOrderBy('s.ordering')
        ;

        if ($this->getCurrentAppName() != 'backend') {
            $queryBuilder->innerJoin('s.translations', 'st')
                ->andWhere('st.active = true')
                ->andWhere('st.locale = :locale')
                ->setParameter('locale', $this->getLocale())
            ;
        }

        return $queryBuilder;
    }

    /**
     * Find All For Tree
     *
     * @param integer|null $appId
     * @param array $excludedSectionIds
     * @return array
     */
    public function findAllForTree($appId = null, $excludedSectionIds = array())
    {
        $queryBuilder = $this->getTreeFromQB(null, 's')
            ->select('s','st')
            ->innerjoin('s.mappings', 'm')
            ->leftJoin('s.sectionNavigations','sn')
            ->leftJoin('sn.navigation','n')
            ->groupBy('s.id')
            ->orderBy('s.ordering','ASC')
            ->addOrderBy('sn.ordering','ASC');

        if ($appId !== null) {
            $queryBuilder
                ->innerJoin('s.app','a')
                ->andWhere('a.id = :appId')
                ->setParameter('appId',$appId);
        }

        if (!empty($excludedSectionIds)) {
            $queryBuilder
                ->andWhere('s.id NOT IN (:excludedSectionIds)')
                ->setParameter('excludedSectionIds',$excludedSectionIds);
        }

        if ($this->getCurrentAppName() == 'backend') {
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
     * Find By Navigation and App
     *
     * @param integer $navigationId
     * @param integer $appId
     *
     * @return array
     */
    public function findByNavigationAndApp($navigationId, $appId)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st')
            ->innerJoin('s.sectionNavigations', 'sn')
            ->where('s.app = :appId')
            ->andWhere('sn.navigation = :navigationId')
            ->orderBy('sn.ordering')

            ->setParameter('appId', $appId)
            ->setParameter('navigationId', $navigationId);

        if ($this->getCurrentAppName() != 'backend') {
            $queryBuilder->innerJoin('s.translations', 'st')
                ->andWhere('st.active = true')
                ->andWhere('st.locale = :locale')
                ->setParameter('locale', $this->getLocale());
        }

        $result = $queryBuilder->getQuery()->getResult();

        $this->buildTree(
            $this->getTreeFromQB($this->getNodeIds($result), 's')
                ->orderBy('s.ordering', 'ASC')
                ->addOrderBy('st.name', 'ASC')
                ->getQuery()->getResult()
        );

        return $result;
    }

    /**
     * @param $appId
     * @return mixed
     */
    public function findByAppJoinChildren($appId)
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->select('s', 'st', 'sm', 'c', 'ct', 'cm')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('s.mappings', 'sm')
            ->leftJoin('s.children', 'c')
            ->leftJoin('c.translations', 'ct')
            ->leftJoin('c.mappings', 'cm')
            ->where('s.app = :appId')
            ->orderBy('s.ordering')
            ->addOrderBy('st.name')
            ->setParameter('appId', $appId->getId());

        return $this->processQuery($queryBuilder);
    }

    /**
     * @param null $appId
     * @return mixed
     */
    public function findRootsWithoutNavigation($appId = null)
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

        if ($appId) {
            $queryBuilder->andWhere('s.app = :appId');
            $queryBuilder->setParameter('appId', $appId);
        }

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
                ->getQuery()->getSingleScalarResult();
        } catch (\Exception $e) {
            return null;
        }
    }
}
