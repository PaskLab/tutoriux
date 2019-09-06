<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;

/**
 * Class Mapping
 * @package App\Entity
 */
class Mapping implements EntityInterface
{
    use EntityUtils;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $target;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var Section
     */
    private $section;

    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var string
     */
    private $context = 'site';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return ($this->id) ? ($this->type . ' ' . $this->target) : 'New mapping';
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Mapping
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set target
     *
     * @param string $target
     *
     * @return Mapping
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering
     *
     * @return Mapping
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get ordering
     *
     * @return integer
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Set section
     *
     * @param  Section $section
     * @return Mapping
     */
    public function setSection(Section $section = null)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     *
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set navigation
     *
     * @param  Navigation $navigation
     * @return Mapping
     */
    public function setNavigation(Navigation $navigation = null)
    {
        $this->navigation = $navigation;

        return $this;
    }

    /**
     * Get navigation
     *
     * @return Navigation
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     * @return $this
     */
    public function setContext(string $context)
    {
        $this->context = $context;

        return $this;
    }
}
