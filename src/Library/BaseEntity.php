<?php

namespace App\Library;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Services\ApplicationCore;

/**
 * Class BaseEntity
 * @package App\Library
 */
abstract class BaseEntity implements EntityInterface, NavigationElementInterface
{
    /**
     * The element is currently selected.
     * Used in navigations.
     *
     * @var boolean
     */
    protected $selected;

    /**
     * The level of the element in the navigation
     *
     * @var integer
     */
    protected $level;

    /**
     * The parent element
     *
     * @var BaseEntity
     */
    protected $parentElement;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $routeParams = [];

    /**
     * Returns the entity name without its path
     *
     * @return string
     */
    public function getEntityName()
    {
        $className = get_class($this);
        $classNameTokens = explode('\\', $className);

        return array_pop($classNameTokens);
    }

    /**
     * Gets the children
     *
     * @return array
     */
    public function getChildren()
    {
        return array();
    }

    /**
     * Return true if the entity has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return false;
    }

    /**
     * Gets the Parent
     *
     * @return BaseEntity
     */
    public function getParent()
    {
        return $this->parentElement;
    }

    /**
     * Sets the Parent
     *
     * @param object $parent The parent object
     */
    public function setParent($parent)
    {
        $this->parentElement = $parent;
    }

    /**
     * Returns true if selected
     *
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * Sets the selected state
     *
     * @param boolean $bool The selected state
     */
    public function setSelected($bool)
    {
        $this->selected = $bool;
    }

    /**
     * Returns true if active
     *
     * @return bool
     */
    public function isActive()
    {
        return false;
    }

    /**
     * Returns true if editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns true if deletable
     *
     * @return bool
     */
    public function isDeletable()
    {
        if (!$this->getId()) {
            return false;
        }

        if (method_exists($this, 'getDeleteRestrictions')) {
            foreach ($this->getDeleteRestrictions() as $method) {

                $result = $this->$method();

                if ((is_bool($result) && $result == true) || (!is_bool($result) && count($result))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get Level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set Level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @param $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param array $routeParams
     * @return $this
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getRouteParams(array $params = []): array
    {
        return array_merge($this->routeParams, $params);
    }
}
