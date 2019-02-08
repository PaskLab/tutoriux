<?php

namespace App\Library;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Services\ApplicationCore;

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
     * @return string
     */
    public function getRoute(): string;

    /**
     * @param array $params
     * @return array
     */
    public function getRouteParams(array $params = []): array;

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
