<?php

namespace App\Entity;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * SectionTranslation
 */
class SectionTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation;
    use TutoriuxORMBehaviors\Sluggable\Sluggable;
    use TutoriuxORMBehaviors\Metadatable\Metadatable;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var boolean $active
     */
    private $active;

    /**
     * @return int
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
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get Sluggable Fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return ['name'];
    }

    /**
     * Returns the combination of fields for which the slug must be unique
     * An empty array mean that all slug will be unique
     *
     * @return array
     */
    public function getUniqueBy()
    {
        return ['name', 'locale'];
    }
}