<?php

namespace App\Library;

/**
 * Interface EntityInterface
 * @package App\Library
 */
interface EntityInterface
{
    /**
     * @return mixed
     */
    public function __toString();

    /**
     * @return string
     */
    public function getEntityName(): string;

    /**
     * @return mixed
     */
    public function isActive(): bool;

    /**
     * @return mixed
     */
    public function isEditable(): bool;

    /**
     * @return bool
     */
    public function isDeletable(): bool;
}
