<?php

namespace App\Library;

use App\Entity\Section;
use App\Library\NavigationElementInterface;

/**
 * Interface ApplicationCoreInterface
 * @package App\Services
 */
interface ApplicationCoreInterface
{
    /**
     * Init
     *
     * @abstract
     */
    public function init();

    /**
     * @return bool
     */
    public function isInitialized() : bool;

    /**
     * Get the current section entity
     *
     * @return Section
     */
    public function getSection();

    /**
     * @param NavigationElementInterface $element
     */
    public function addNavigationElement(NavigationElementInterface $element);

    /**
     * @return NavigationElementInterface
     */
    public function getElement();

    /**
     * @return string
     */
    public function getEditLocale() : string;

    /**
     * @return bool
     */
    public function isEditLocaleEnabled(): bool;

    /**
     * @param bool $editLocaleEnabled
     * @return $this
     */
    public function setEditLocaleEnabled(bool $editLocaleEnabled);
}
