<?php

namespace App\Library;

/**
 * Interface NavigationElementInterface
 * @package App\Library
 */
interface NavigationElementInterface
{
    /**
     * isSelected
     *
     * @abstract
     */
    public function isSelected();

    /**
     * setSelected
     *
     * @param boolean $bool Selected State
     *
     * @abstract
     */
    public function setSelected($bool);

    /**
     * Get Children
     *
     * @abstract
     */
    public function getChildren();

    /**
     * Has Children
     *
     * @abstract
     */
    public function hasChildren();

    /**
     * Get Parent
     *
     * @abstract
     */
    public function getParent();

    /**
     * Set Parent
     *
     * @param object $parent The Parent
     *
     * @abstract
     */
    public function setParent($parent);

    /**
     * Get Level
     *
     * @abstract
     */
    public function getLevel();

    /**
     * Set Level
     *
     * @param integer $level The Level
     *
     * @abstract
     */
    public function setLevel($level);
}