<?php

namespace App\Library\Component;

/**
 * Class TreeEntitySorter
 * @package App\Library\Component
 */
class TreeEntitySorter
{
    /**
     * Sort Entities
     *
     * Sort the entities to a parent/child tree view
     *
     * @param $entities
     *
     * @return array
     */
    public function sortEntities($entities)
    {
        $tree = array();

        foreach ($entities as $entity) {

            $entity->setChildren(null);
            $tree[$entity->getId()] = $entity;
        }

        foreach ($tree as $entity) {

            if ($parent = $entity->getParent()) {
                if (isset($tree[$parent->getId()])) {
                    $tree[$parent->getId()]->addChildren($entity);
                }
            }
        }

        foreach ($tree as $entityId => $entity) {

            if ($entity->getParent()) {
                unset($tree[$entityId]);
            }
        }

        $flatTree = $this->flattenChildren($tree, array());

        // Remove the children, otherwise the children must be a PersistentCollection
        foreach ($flatTree as $entity) {
            $entity->setChildren(null);
        }

        return $flatTree;
    }

    /**
     * @param $entities
     * @param $flatTree
     * @return array
     */
    protected function flattenChildren($entities, $flatTree)
    {
        foreach ($entities as $entity) {
            $flatTree[] = $entity;
            if ($entity->hasChildren()) {
                $flatTree = $this->flattenChildren($entity->getChildren(), $flatTree);
            }
        }

        return $flatTree;
    }

}
