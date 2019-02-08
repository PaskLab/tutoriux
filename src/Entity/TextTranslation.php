<?php

namespace App\Entity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * TextTranslation
 */
class TextTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $anchor
     */
    private $anchor;

    /**
     * @var boolean $active
     */
    private $active;

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
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
     * Set anchor
     *
     * @param string $anchor
     */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    /**
     * Get anchor
     *
     * @return string
     */
    public function getAnchor()
    {
        return $this->anchor;
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
     * Validate the sub-fields of a collapsible text
     *
     * @param ExecutionContextInterface $context The Execution Context
     */
    public function isCollapsibleValid(ExecutionContextInterface $context)
    {
        if ($this->translatable->getCollapsible() && false == $this->getName()) {
            $context
                ->buildViolation('A collapsible text must have a name')
                ->atPath('name')
                ->addViolation();
        }
    }

}
