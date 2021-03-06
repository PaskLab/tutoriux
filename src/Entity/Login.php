<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Login
 * @package App\Entity
 */
class Login implements EntityInterface
{
    use EntityUtils,
        TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $ip
     */
    private $ip;

    /**
     * @var boolean $success
     */
    private $success;

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
        if (false == $this->id) {
            return 'New login';
        }

        if ($this->username) {
            return $this->username . ' - ' . $this->ip;
        }

        return '';
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set ip
     *
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set success
     *
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * Get success
     *
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->success;
    }
}
