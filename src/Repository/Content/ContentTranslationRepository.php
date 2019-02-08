<?php

namespace App\Repository\Content;

use App\Library\BaseEntityRepository;

/**
 * Class ContentTranslationRepository
 * @package App\Repository\Content
 */
class ContentTranslationRepository extends BaseEntityRepository
{
    /**
     * @param $contentType
     * @param $contentId
     * @param $locale
     * @return mixed
     */
    public function findWithLocale($contentType, $contentId, $locale)
    {
        $queryBuilder = $this->createQueryBuilder('ct')
            ->select('ct')
            ->innerJoin('ct.translatable', 'c')
            ->where('c.resourceType = :contentType AND c.resourceId = :contentId AND ct.locale = :locale')
            ->setParameters([
                'contentType' => $contentType,
                'contentId' => $contentId,
                'locale' => $locale
            ])
            ->setMaxResults(1);

        return $this->processQuery($queryBuilder, true);
    }
}