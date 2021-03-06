<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use App\Library\NavigationElementInterface;
use App\Library\Traits\NavigationNodeBridge;
use Doctrine\Common\Collections\ArrayCollection;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Tree\NodeInterface;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;

/**
 * Class Section
 * @package App\Entity
 */
class Section implements EntityInterface, NodeInterface, NavigationElementInterface, TranslatableInterface
{
    use EntityUtils,
        NavigationNodeBridge,
        TutoriuxORMBehaviors\Translatable\Translatable,
        TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Tree\MaterializedPath;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var boolean
     */
    private $removeFromUrl = false;

    /**
     * @var array
     */
    private $sectionNavigations;

    /**
     * @var ArrayCollection
     */
    private $texts;

    /**
     * @var ArrayCollection
     */
    private $roles;

    /**
     * @var ArrayCollection
     */
    private $mappings;

    /**
     * Construct
     *
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->mappings = new ArrayCollection();
        $this->sectionNavigations = new ArrayCollection();
        $this->texts = new ArrayCollection();
    }

    public function __toString()
    {
        if (false == $this->id) {
            return 'New section';
        }

        if ($name = $this->getName()) {
            return $name;
        }

        // No translation found in the current locale
        return '';
    }

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
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get parents slugs
     *
     * @return array
     */
    public function getParentsSlugs()
    {
        $slugs = array();

        /** @var $parent Section */
        foreach ($this->getParents() as $parent) {
            $slugs[] = $parent->getSlug();
        }

        return $slugs;
    }

    /**
     * getLevel
     *
     * @return integer
     */
    public function getLevel()
    {
        return count($this->getParents()) + 1;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute(): string
    {
        return 'section_id_' . $this->id;
    }

    /**
     * Get Frontend route params
     *
     * @param array $params Array of params to get
     *
     * @return iterable
     */
    public function getRouteParams(iterable $params = array()): iterable
    {
        return array_merge($this->routeParams, $params);
    }

    /**
     * Set ordering
     *
     * @param integer $ordering The ordering number
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
     * Get sectionNavigations
     *
     * @return ArrayCollection
     */
    public function getSectionNavigations()
    {
        return $this->sectionNavigations;
    }

    /**
     * Set sectionNavigations
     *
     * @param $sectionNavigations
     */
    public function setSectionNavigations($sectionNavigations)
    {
        $this->sectionNavigations = $sectionNavigations;
    }

    /**
     * Add Section
     *
     * @param Section $children The Section to add as a child
     */
    public function addSection(Section $children)
    {
        $this->children[] = $children;
    }

    /**
     * Add sectionNavigations
     *
     * @param SectionNavigation $sectionNavigation SectionNavigation to add
     */
    public function addSectionNavigation(SectionNavigation $sectionNavigation)
    {
        $this->sectionNavigations[] = $sectionNavigation;
    }

    /**
     * Add text
     *
     * @param Text $text Text to add
     */
    public function addText(Text $text)
    {
        $this->texts[] = $text;
    }

    /**
     * @return ArrayCollection
     */
    public function getTexts()
    {
        return $this->texts;
    }

    /**
     * @param ArrayCollection $texts
     */
    public function setTexts(ArrayCollection $texts)
    {
        $this->texts = $texts;
    }

    /**
     * Returns the complete path of the section (Section / Sub-Section / Sub-sub-section ... )
     *
     * @return string
     */
    public function getHierarchicalName()
    {
        $return = $this->__toString();
        if ($this->getParent()) {
            $return = $this->parent->getHierarchicalName() . " / " . $return;
        }

        return $return;
    }

    /**
     * Checks if the section is in a specific navigation
     *
     * @param $navigationName
     *
     * @return bool
     */
    public function hasNavigation($navigationName)
    {
        foreach ($this->sectionNavigations as $sectionNavigation) {
            if ($sectionNavigation->getNavigation()->getName() == $navigationName) {
                return true;
            }
        }

        return false;
    }

    public function getHeadExtra()
    {
        return '';
    }

    /**
     * Get the navigations
     *
     * @return ArrayCollection
     */
    public function getNavigations()
    {
        $navigations = new ArrayCollection();

        foreach ($this->sectionNavigations as $sectionNavigation) {
            $navigations[] = $sectionNavigation->getNavigation();
        }

        return $navigations;
    }

    /**
     * Set the navigations
     *
     * @param $navigations ArrayCollection
     */
    public function setNavigations($navigations)
    {
        // Removing unassociated navigations
        foreach ($this->sectionNavigations as $key => $sectionNavigation) {
            if (false == $navigations->contains($sectionNavigation->getNavigation())) {
                unset($this->sectionNavigations[$key]);
            }
        }

        foreach ($navigations as $navigation) {

            // Already associated
            foreach ($this->sectionNavigations as $sectionNavigation) {
                if ($sectionNavigation->getNavigation() === $navigation) {
                    continue 2;
                }
            }

            // Has to be associated
            $sectionNavigation = new SectionNavigation();
            $sectionNavigation->setNavigation($navigation);
            $sectionNavigation->setSection($this);

            $this->sectionNavigations[] = $sectionNavigation;
        }
    }

    /**
     * Add roles
     *
     * @param  Role $roles
     * @return Section
     */
    public function addRole(Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Set Roles
     *
     * @param ArrayCollection $roles
     */
    public function setRoles(ArrayCollection $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Remove roles
     *
     * @param Role $roles
     */
    public function removeRole(Role $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Remove texts
     *
     * @param Text $texts
     */
    public function removeText(Text $texts)
    {
        $this->texts->removeElement($texts);
    }

    /**
     * Remove sectionNavigations
     *
     * @param SectionNavigation $sectionNavigations
     */
    public function removeSectionNavigation(SectionNavigation $sectionNavigations)
    {
        $this->sectionNavigations->removeElement($sectionNavigations);
    }

    /**
     * Add mappings
     *
     * @param Mapping $mappings
     * @return Section
     */
    public function addMapping(Mapping $mappings)
    {
        $this->mappings[] = $mappings;

        return $this;
    }

    /**
     * Remove mappings
     *
     * @param Mapping $mappings
     */
    public function removeMapping(Mapping $mappings)
    {
        $this->mappings->removeElement($mappings);
    }

    /**
     * Get mappings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Set icon
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return boolean
     */
    public function isRemoveFromUrl()
    {
        return $this->removeFromUrl;
    }

    /**
     * @param boolean $removeFromUrl
     */
    public function setRemoveFromUrl($removeFromUrl)
    {
        $this->removeFromUrl = $removeFromUrl;
    }
}