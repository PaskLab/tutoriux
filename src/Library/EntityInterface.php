<?php

namespace App\Library;

/**
 * Interface EntityInterface
 * @package App\Library
 */
interface EntityInterface
{
    /**
     * __toString
     *
     * @abstract
     */
    public function __toString();

    /**
     * Is Active
     *
     * @abstract
     */
    public function isActive();

    /**
     * Is Editable
     *
     * @abstract
     */
    public function isEditable();

    /**
     * Is Deletable
     *
     * @abstract
     */
    public function isDeletable();
}
