<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class SectionNavigation
 * @package App\Entity
 */
class SectionNavigation implements EntityInterface
{
    use EntityUtils,
        TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New section-navigation relation';
        }

        if ($this->section && $this->navigation) {
            return $this->section . ' - ' . $this->navigation;
        }

        return '';
    }

    /**
     * Set ordering
     *
     * @param integer $ordering
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
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
     * @param Section $section Section object
     */
    public function setSection(Section $section)
    {
        $this->section = $section;
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
     * @param Navigation $navigation Navigation object
     */
    public function setNavigation(Navigation $navigation)
    {
        $this->navigation = $navigation;
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

}
