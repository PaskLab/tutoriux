<?php

namespace App\Repository\Document;

use Doctrine\ORM\NonUniqueResultException;
use App\Library\BaseEntityRepository;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;

/**
 * Class DocumentRepository
 * @package App\Repository\Document
 */
class DocumentRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;

    /**
     * @param $documentId
     * @param $locale
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findWithLocale($documentId, $locale)
    {
        return $this->createQueryBuilder('d')
            ->select('d', 'dt', 'ds', 'dst')
            ->innerJoin('d.section', 'ds')
            ->innerJoin('d.translations', 'dt')
            ->innerJoin('ds.translations', 'dst')
            ->where('d.id = :documentId AND dt.locale = :locale AND dst.locale = :locale')
            ->setParameters([
                'documentId' => $documentId,
                'locale' => $locale
            ])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $documentId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneWithAllLocale($documentId)
    {
        return $this->createQueryBuilder('d')
            ->select('d', 'dt')
            ->leftJoin('d.translations', 'dt')
            ->where('d.id = :documentId')
            ->setParameter('documentId', $documentId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $documentId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function countTranslations($documentId)
    {
        $queryBuilder  = $this->createQueryBuilder('d');
        $queryBuilder
            ->select($queryBuilder->expr()->count('dt'))
            ->leftJoin('d.translations', 'dt')
            ->where('d.id = :documentId')
            ->setParameter('documentId', $documentId);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}