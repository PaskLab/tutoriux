<?php

namespace App\Entity;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RoleTranslation
 * @package App\Entity
 */
class RoleTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

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

}