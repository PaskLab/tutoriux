<?php

namespace App\Repository\Content;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use App\Library\BaseEntityRepository;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;

/**
 * Class ContentRepository
 * @package App\Repository\Content
 */
class ContentRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;

    /**
     * @param $contentType
     * @param $contentId
     * @param $locale
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findWithLocale($contentType, $contentId, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c', 'ct')
            ->innerJoin('c.translations', 'ct')
            ->where('c.resourceType = :contentType AND c.resourceId = :contentId AND ct.locale = :locale')
            ->setParameters([
                'contentType' => $contentType,
                'contentId' => $contentId,
                'locale' => $locale
            ])
            ->setMaxResults(1);

        return $this->processQuery($queryBuilder, true);
    }

    /**
     * @param $contentType
     * @param $contentId
     * @return null
     */
    public function findWithAllLocale($contentType, $contentId)
    {
        $result = $this->createQueryBuilder('c')
            ->select('c', 'ct')
            ->leftJoin('c.translations', 'ct')
            ->where('c.resourceType = :contentType AND c.resourceId = :contentId')
            ->setParameters([
                'contentType' => $contentType,
                'contentId' => $contentId
            ])->getQuery()->getResult();

        return ($result) ? $result[0] : null;
    }

    /**
     * @param $contentId
     * @param $locale
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function countVersions($contentId, $locale)
    {
        $queryBuilder  = $this->createQueryBuilder('c');
        $queryBuilder
            ->select($queryBuilder->expr()->count('cv'))
            ->innerJoin('c.translations', 'ct')
            ->innerJoin('ct.versions', 'cv')
            ->where('c.id = :contentId AND ct.locale = :locale')
            ->setParameters([
                'contentId' => $contentId,
                'locale' => $locale
            ]);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}