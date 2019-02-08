<?php

namespace App\Repository\School;

use Doctrine\ORM\EntityRepository;

/**
 * Class RequestRepository
 * @package App\Repository\School
 */
class RequestRepository extends EntityRepository
{
    /**
     * @param $resourceType
     * @param $resourceId
     * @param $locale
     * @return mixed
     */
//    public function findActiveRequest($resourceType, $resourceId, $locale)
//    {
//        return $this->createQueryBuilder('r')
//            ->select('r')
//            ->where('r.resourceType = :resource_type')
//            ->andWhere('r.resourceId = :resource_id')
//            ->andWhere('r.locale = :locale')
//            ->andWhere('r.status IN :active_status')
//            ->setParameters([
//                'resource_type' => $resourceType,
//                'resource_id' => $resourceId,
//                'locale' => $locale,
//                'active_status' => ['submitted', 'in treatment']
//            ])
//            ->setMaxResults(1)
//            ->getQuery()->getOneOrNullResult();
//    }
}
