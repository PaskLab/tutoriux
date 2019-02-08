<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MessageRepository
 * @package App\Entity
 */
class MessageRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return mixed
     */
    public function countUnreadMessage(User $user)
    {
        $queryBuilder  = $this->createQueryBuilder('m');
        $queryBuilder
            ->select($queryBuilder->expr()->count('m'))
            ->innerJoin('m.user', 'mu')
            ->innerJoin('m.createdBy', 'mc')
            ->where('mu.id = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('m.viewed <> TRUE')
        ;

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @param $view
     * @param null $filter
     * @return mixed
     */
    public function countMessage(User $user, $view, $filter = null)
    {
        $queryBuilder  = $this->createQueryBuilder('m');
        $queryBuilder
            ->select($queryBuilder->expr()->count('m'))
            ->innerJoin('m.user', 'mu')
            ->innerJoin('m.createdBy', 'mc')
        ;

        switch ($view) {
            case 'sent':
                $queryBuilder
                    ->where('mc.id  = :userId')
                    ->setParameter('userId', $user->getId());
                break;
            case 'trash':
                $queryBuilder
                    ->where('mu.id = :userId AND m.deletedAt <= :now')
                    ->setParameter('userId', $user->getId())
                    ->setParameter('now', new \DateTime());
                break;
            default:
                $queryBuilder
                    ->where('mu.id = :userId AND (m.deletedAt > :now OR m.deletedAt IS NULL)')
                    ->setParameter('userId', $user->getId())
                    ->setParameter('now', new \DateTime());
        }

        if ($filter) {
            $userAlias = ('sent' == $view) ? 'mu' : 'mc';
            $concat = 'COALESCE(NULLIF(CONCAT(CONCAT('.$userAlias.'.firstname, '
                .$queryBuilder->expr()->literal(' ').'), '.$userAlias.'.lastname), '
                .$queryBuilder->expr()->literal(' ').'), '.$userAlias.'.username)';
             $queryBuilder
                ->andWhere('LOWER(m.title) LIKE :filter OR LOWER('.$concat.') LIKE :filter')
                ->setParameter('filter', '%'.mb_strtolower($filter, 'UTF-8').'%');
        }


        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @param $view
     * @param $length
     * @param $page
     * @param null $filter
     * @return mixed
     */
    public function findMessages(User $user, $view,  $length, $page, $filter = null)
    {
        $offset = ($page - 1) * $length;

        $queryBuilder  = $this->createQueryBuilder('m');
        $queryBuilder
            ->select('m', 'mu', 'mc')
            ->innerJoin('m.user', 'mu')
            ->innerJoin('m.createdBy', 'mc')
            ->setFirstResult($offset)
            ->setMaxResults($length)
            ->orderBy('m.createdAt', 'DESC')
        ;

        switch ($view) {
            case 'sent':
                $queryBuilder
                    ->where('mc.id  = :userId')
                    ->setParameter('userId', $user->getId());
                break;
            case 'trash':
                $queryBuilder
                    ->where('mu.id = :userId AND m.deletedAt <= :now')
                    ->setParameter('userId', $user->getId())
                    ->setParameter('now', new \DateTime());
                break;
            default:
                $queryBuilder
                    ->where('mu.id = :userId AND (m.deletedAt > :now OR m.deletedAt IS NULL)')
                    ->setParameter('userId', $user->getId())
                    ->setParameter('now', new \DateTime());
        }

        if ($filter) {
            $userAlias = ('sent' == $view) ? 'mu' : 'mc';
            $concat = 'COALESCE(NULLIF(CONCAT(CONCAT('.$userAlias.'.firstname, '
                .$queryBuilder->expr()->literal(' ').'), '.$userAlias.'.lastname), '
                .$queryBuilder->expr()->literal(' ').'), '.$userAlias.'.username)';
            $queryBuilder
                ->andWhere('LOWER(m.title) LIKE :filter OR LOWER('.$concat.') LIKE :filter')
                ->setParameter('filter', '%'.mb_strtolower($filter, 'UTF-8').'%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
