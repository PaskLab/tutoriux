<?php

namespace App\Entity\Content;

use Doctrine\ORM\Mapping as ORM;
use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;

/**
 * Class Content
 * @package App\Entity\Content
 */
class Content implements EntityInterface, TranslatableInterface
{
    use EntityUtils,
        TutoriuxORMBehaviors\Translatable\Translatable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * @var integer
     */
    private $resourceId;

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
        return ($this->getTranslation()) ?: 'Content';
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param $resourceType
     * @return $this
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param $resourceId
     * @return $this
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }
}