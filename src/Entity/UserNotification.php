<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserNotification
 * @package App\Entity
 */
class UserNotification
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $viewed;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->viewed = false;
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
     * Set viewed
     *
     * @param boolean $viewed
     * @return UserNotification
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * Get viewed
     *
     * @return boolean 
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserNotification
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set notification
     *
     * @param Notification $notification
     * @return UserNotification
     */
    public function setNotification(Notification $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}