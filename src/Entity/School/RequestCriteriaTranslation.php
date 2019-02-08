<?php

namespace App\Entity\School;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RequestCriteriaTranslation
 * @package App\Entity\School
 */
class RequestCriteriaTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation,
        TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $criteria;

    /**
     * @var string
     */
    private $details;

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
     * Set criteria
     *
     * @param string $criteria
     * @return RequestCriteriaTranslation
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get criteria
     *
     * @return string
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set details
     *
     * @param string $details
     * @return RequestCriteriaTranslation
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }
}
