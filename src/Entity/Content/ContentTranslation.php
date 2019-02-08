<?php

namespace App\Entity\Content;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class ContentTranslation
 * @package App\Entity\Content
 */
class ContentTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation,
        TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $content;

    /**
     * Schema version of json embed in $content
     * @var integer
     */
    private $schemaVersion;

    /**
     * @var ArrayCollection
     */
    private $versions;

    /**
     * DocumentTranslation constructor.
     */
    public function __construct()
    {
        $this->content = '[]';
        $this->versions = new ArrayCollection();
        $this->schemaVersion = 1;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->name) ?: 'Content Translation';
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getSchemaVersion()
    {
        return $this->schemaVersion;
    }

    /**
     * @param $schemaVersion
     * @return $this
     */
    public function setSchemaVersion($schemaVersion)
    {
        $this->schemaVersion = $schemaVersion;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param $versions
     * @return $this
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;

        return $this;
    }

    /**
     * @param ContentVersion $version
     * @return $this
     */
    public function addVersion(ContentVersion $version)
    {
        $this->versions->add($version);

        return $this;
    }

    /**
     * @param ContentVersion $version
     * @return $this
     */
    public function removeVersion(ContentVersion $version)
    {
        $this->versions->removeElement($version);

        return $this;
    }
}