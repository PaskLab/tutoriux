<?php

namespace App\Repository\Media;

use App\Entity\User,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors,
    Tutoriux\DoctrineBehaviorsBundle\Model\Repository\NodeRepositoryInterface,
    App\Library\BaseEntityRepository;

/**
 * Class FolderRepository
 * @package App\Repository\Media
 */
class FolderRepository extends BaseEntityRepository implements NodeRepositoryInterface
{
    use TutoriuxORMBehaviors\Repository\MaterializedPathRepository;

    /**
     * @param App $app
     * @param User $user
     * @return array
     */
    public function findFoldersTree(App $app, User $user)
    {
        $queryBuilder = $this->getRootNodesQB('f')
            ->select('f')
            ->innerJoin('f.app', 'a')
            ->andWhere('a.id = :appId')
            ->setParameter('appId', $app->getId())
            ->orderBy('f.name', 'ASC');

        if ($app->getId() != AppRepository::BACKEND_APP_ID) {
            $queryBuilder
                ->innerJoin('f.createdBy', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        $result = $queryBuilder->getQuery()->getResult();

        $this->buildTree($this->findTreeFrom($this->getNodeIds($result), 'f'));

        return $result;
    }
}