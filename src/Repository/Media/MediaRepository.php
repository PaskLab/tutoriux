<?php

namespace App\Repository\Media;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use App\Library\BaseEntityRepository,
    App\Entity\User;

/**
 * Class MediaRepository
 * @package App\Repository\Media
 */
class MediaRepository extends BaseEntityRepository
{
    /**
     * @return array|QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.hidden = false');

        return $this->processQuery($qb);
    }

    /**
     * @param $type
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findByType($type)
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->andWhere('m.hidden = false')

            ->setParameter('type', $type);

        return $this->processQuery($qb);
    }

    /**
     * @param string $folderId
     * @param string $type
     * @param string $view
     * @param string $sort
     * @param string $text
     * @param User|null $user
     * @return QueryBuilder|mixed
     * @throws NonUniqueResultException
     */
    public function findByFolderType($folderId = 'root', $type = 'any', $view = 'ckeditor', $sort = 'newer', $text = '', User $user = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.app', 'a')
            ->where('a.id = :appId')
//            ->setParameter('appId', $app->getId())
            ->groupBy('m')
        ;

//        if ($app->getId() != AppRepository::BACKEND_APP_ID) {
            $qb
                ->innerJoin('m.createdBy', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId())
            ;
//        }

        if (is_numeric($folderId)) {
            $qb->andWhere('m.folder = :folderId')
                ->setParameter('folderId', $folderId);

        } elseif ('root' == $folderId) {
            $qb->andWhere('m.folder IS NULL');
        }

        if ('any' != $type) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $type);
        } elseif ('ck_inline' == $view) {
            $qb->andWhere('m.type in (:types)')
                ->setParameter('types', ['image', 'document']);
        }

        switch ($sort) {
            case 'alpha':
                $qb->addOrderBy('m.name', 'ASC');
                break;
            case 'newer':
                $qb->addOrderBy('m.createdAt', 'DESC');
                break;
            default:
                $qb->addOrderBy('m.createdAt', 'ASC');
        }

        $qb->andWhere('m.hidden = false');

        if ('' != trim($text)) {
            $qb->andWhere('LOWER(m.name) LIKE :text')
                ->setParameter('text', '%' . mb_strtolower($text, 'UTF-8') . '%');
        }

        return $this->processQuery($qb);
    }
}