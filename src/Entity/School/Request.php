<?php

namespace App\Entity\School;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping as ORM;

use App\Entity\User,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Request
 * @package App\Entity\School
 */
class Request
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable;

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
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $checklist;

    /**
     * @var \DateTime
     */
    private $publishDate;

    /**
     * @var User
     */
    private $applicant;

    /**
     * @var ArrayCollection
     */
    private $assessors;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->assessors = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Publication Request';
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

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Request
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $checklist
     * @return $this
     */
    public function setChecklist($checklist)
    {
        $this->checklist = $checklist;

        return $this;
    }

    /**
     * @return string
     */
    public function getChecklist()
    {
        return $this->checklist;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param \Datetime $publishDate
     * @return $this
     */
    public function setPublishDate(\Datetime $publishDate = null)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * @return User
     */
    public function getApplicant()
    {
        return $this->applicant;
    }

    /**
     * @param $applicant
     * @return $this
     */
    public function setApplicant($applicant)
    {
        $this->applicant = $applicant;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessors()
    {
        return $this->assessors;
    }

    /**
     * @param ArrayCollection $assessors
     * @return $this
     */
    public function setAssessors(ArrayCollection $assessors)
    {
        $this->assessors = $assessors;

        return $this;
    }

    /**
     * @param RequestAssessor $assessor
     * @return $this
     */
    public function addAssessor(RequestAssessor $assessor)
    {
        $this->assessors->add($assessor);

        return $this;
    }

    /**
     * @param RequestAssessor $assessor
     * @return $this
     */
    public function removeAssessor(RequestAssessor $assessor)
    {
        $this->assessors->removeElement($assessor);

        return $this;
    }
}
