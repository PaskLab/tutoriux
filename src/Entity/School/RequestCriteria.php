<?php

namespace App\Entity\School;

use App\Library\BaseEntity,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RequestCriteria
 * @package AdminBundle\Entity
 */
class RequestCriteria extends BaseEntity
{
    use TutoriuxORMBehaviors\Translatable\Translatable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var boolean
     */
    private $optional;

    /**
     * @var integer
     */
    private $ordering;

    /**
     * @var string
     */
    private $contentType;

    /**
     * RequestCriteria constructor.
     */
    public function __construct()
    {
        $this->contentType = json_encode([]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New Criteria';
        }

        if ($criteria = $this->getCriteria()) {
            return $criteria;
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
     * Set active
     *
     * @param boolean $active
     * @return RequestCriteria
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set optional
     *
     * @param boolean $optional
     * @return RequestCriteria
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * Get optional
     *
     * @return boolean 
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @param $ordering
     * @return $this
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return json_decode($this->contentType);
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = json_encode($contentType);

        return $this;
    }
}
