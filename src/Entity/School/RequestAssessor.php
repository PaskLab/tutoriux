<?php

namespace App\Entity\School;

use App\Entity\User,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class RequestAssessor
 * @package App\Entity\School
 */
class RequestAssessor
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $assessor;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Request Assessor';
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
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return User
     */
    public function getAssessor()
    {
        return $this->assessor;
    }

    /**
     * @param $assessor
     * @return $this
     */
    public function setAssessor($assessor)
    {
        $this->assessor = $assessor;

        return $this;
    }
}
