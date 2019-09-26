<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Navigation
 * @package App\Entity
 */
class Navigation implements EntityInterface
{
    use EntityUtils;

    /**
     * Basic Navigation codes
     * Shall reflect the codes stored in the database
     */
    public const SECTION_BAR = 'section_bar';
    public const SECTION_MODULE_BAR = 'section_module_bar';
    public const TOP_MODULE_BAR = 'top_module_bar';
    public const SIDE_MODULE_BAR = 'side_module_bar';
    public const HEADER = 'header';
    public const SECONDARY = 'secondary';
    public const FOOTER = 'footer';

    /**
     * __toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var SectionNavigation
     */
    private $sectionNavigations;

    /**
     * @var ArrayCollection
     */
    private $sections;

    /**
     * @var string
     */
    private $code;

    /**
     * @var ArrayCollection
     */
    private $mappings;

    /**
     * Navigation constructor.
     */
    public function __construct()
    {
        $this->sectionNavigations = new ArrayCollection();
        $this->mappings = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Add sectionNavigation
     *
     * @param SectionNavigation $sectionNavigation
     */
    public function addSectionNavigation(SectionNavigation $sectionNavigation)
    {
        $this->sectionNavigations[] = $sectionNavigation;
    }

    /**
     * Get sections
     *
     * @return ArrayCollection
     */
    public function getSections()
    {
        if (!$this->sections) {

            $this->sections = new ArrayCollection();

            foreach ($this->sectionNavigations as $sectionNavigation) {
                $this->sections[] = $sectionNavigation->getSection();
            }
        }

        return $this->sections;
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
     * @param  Mapping $mappings
     * @return Navigation
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
     * Set code
     *
     * @param  string     $code
     * @return Navigation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
