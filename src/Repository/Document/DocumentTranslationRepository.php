<?php

namespace App\Repository\Document;

use Doctrine\ORM\Query;
use Doctrine\ORM\NonUniqueResultException;
use App\Entity\Section;
use App\Library\BaseEntityRepository;
use App\Entity\User;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;

/**
 * Class DocumentTranslationRepository
 * @package App\Repository\Document
 */
class DocumentTranslationRepository extends BaseEntityRepository implements TranslatableRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\TranslatableEntityRepository;

    /**
     * @param User $user
     * @param $isPublished
     * @param bool $hidePrivate
     * @return array
     */
    public function findUserDocTranslations(User $user, $isPublished, $hidePrivate = true)
    {
        $queryBuilder = $this->createQueryBuilder('dt')
            ->select('d', 'dt')
            ->innerJoin('dt.translatable', 'd')
            ->innerJoin('d.section', 'ds')
            ->innerJoin('ds.translations', 'dst')
            ->where('d.createdBy = :userId OR dt.createdBy = :userId')
            ->setParameter('userId', $user->getId())
            ->andWhere('dt.published = :isPublished')
            ->setParameter('isPublished', $isPublished)
            ->andWhere('dst.locale = :locale')
            ->setParameter('locale', $this->getLocale())
            ->orderBy('dt.name', 'ASC');
        
        if ($hidePrivate) {
            $queryBuilder->andWhere('dt.public = TRUE');
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Section $section
     * @param $locale
     * @return Query
     */
    public function getSubjectDocumentsQuery(Section $section, $locale)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT dt, CASE WHEN dt.locale = :value THEN 1 ELSE 0 END AS HIDDEN sortCondition 
           FROM DocumentBundle\Entity\DocumentTranslation dt
           INNER JOIN dt.translatable d
           WHERE dt.published = TRUE AND d.section = :section
           ORDER BY sortCondition DESC, dt.publicationDate DESC
        ');
        
        $query->setParameters([
            'value' => $locale,
            'section' => $section
        ]);
        
        return $query;
    }

    /**
     * @param Section $section
     * @param string $locale
     * @return mixed
     */
    public function findSubjectDocuments(Section $section, string $locale)
    {
        $query = $this->getSubjectDocumentsQuery($section, $locale);

        return $query->getResult();
    }

    /**
     * @param $documentSlug
     * @param $sectionId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findPublished($documentSlug, $sectionId)
    {
        return $this->createQueryBuilder('dt')
            ->select('dt', 'd', 'ds', 'dst', 'u')
            ->innerJoin('dt.translatable', 'd')
            ->innerJoin('d.section', 'ds')
            ->innerJoin('ds.translations', 'dst')
            ->innerJoin('dt.createdBy', 'u')
            ->where('dt.slug = :documentSlug AND ds.id = :sectionId')
            ->setParameters([
                'documentSlug' => $documentSlug,
                'sectionId' => $sectionId
            ])
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $documentId
     * @return array
     */
    public function getAlternateLanguages($documentId)
    {
        return $this->createQueryBuilder('dt')
            ->select('dt')
            ->where('dt.translatable = :documentId AND dt.published = TRUE')
            ->setParameter('documentId', $documentId)
            ->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param null $rootNodeId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getUserPublishedCount(User $user, $rootNodeId = null)
    {
        $queryBuilder = $this->createQueryBuilder('dt');

        $queryBuilder
            ->select($queryBuilder->expr()->count('dt'))
            ->innerJoin('dt.translatable', 'd')
            ->innerJoin('d.section', 'ds')
            ->where('dt.createdBy = :user')
            ->andWhere('dt.published = TRUE')
            ->setParameter('user', $user);

        if ($rootNodeId) {
            $queryBuilder
                ->andWhere('ds.materializedPath LIKE :rootNodeId')
                ->setParameter('rootNodeId', $rootNodeId.'%');
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}