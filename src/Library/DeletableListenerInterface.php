<?php

namespace App\Library;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface DeletableListenerInterface
 * @package App\Library
 */
interface DeletableListenerInterface
{
    /**
     * Check if this entity can be deleted.
     * @param  Object  $entity
     * @return bool
     */
    public function isDeletable($entity);

    /**
     * @return ArrayCollection
     */
    public function getErrors();

}
