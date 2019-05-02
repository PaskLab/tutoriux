<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use App\Library\TranslatableRepositoryInterface;
use App\Library\BaseEntityRepository;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;

    /**
     * Find with User
     *
     * @return array
     */
    public function findWithUser()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT r FROM SystemBundle:Role r LEFT JOIN r.translations rt INNER JOIN r.users u ORDER BY rt.name ASC')
            ->getResult();
    }

    /**
     * @return array|QueryBuilder|mixed|object[]
     * @throws NonUniqueResultException
     */
    public function findAll()
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r', 'rt', 'u')
            ->leftJoin('r.translations', 'rt')
            ->leftJoin('r.users', 'u')
            ->orderBy('rt.name');

        return $this->processQuery($queryBuilder);
    }

    /**
     * @param $roles
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findAllExcept($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r', 'rt', 'u')
            ->leftJoin('r.translations', 'rt')
            ->leftJoin('r.users', 'u')
            ->where('r.role NOT IN (:roles)')
            ->setParameter('roles', $roles)
            ->orderBy('rt.name');

        return $this->processQuery($queryBuilder);
    }
}
