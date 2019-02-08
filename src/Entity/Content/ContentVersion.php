<?php

namespace App\Entity\Content;

use Doctrine\ORM\Mapping as ORM;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class ContentVersion
 * @package App\Entity\Content
 */
class ContentVersion
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $version;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $schemaVersion;

    /**
     * @var ContentTranslation
     */
    private $master;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Version "'. (($this->tag)?:$this->version) . '" of ' . $this->getMaster()->getName();
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
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param $tag
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @return ContentTranslation
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @param $master
     * @return $this
     */
    public function setMaster(ContentTranslation $master)
    {
        $this->master = $master;

        return $this;
    }
}