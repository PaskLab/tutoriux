<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\Collection,
    Doctrine\Common\Collections\ArrayCollection;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Notification
 * @package App\Entity
 */
class Notification
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var \DateTime
     */
    private $expiration;

    /**
     * @var Collection
     */
    private $users;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $iconColor;

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @var boolean
     */
    private $toastr;

    /**
     * @var string
     */
    private $toastrType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->toastr = false;
        $this->parameters = [];
        $this->routeParameters = [];
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
     * Set token
     *
     * @param string $token
     * @return Notification
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array 
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set expiration
     *
     * @param \DateTime $expiration
     * @return Notification
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * Get expiration
     *
     * @return \DateTime 
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Add users
     *
     * @param UserNotification $users
     * @return Notification
     */
    public function addUser(UserNotification $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param UserNotification $users
     */
    public function removeUser(UserNotification $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconColor()
    {
        return $this->iconColor;
    }

    /**
     * @param $iconColor
     * @return $this
     */
    public function setIconColor($iconColor)
    {
        $this->iconColor = $iconColor;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isToastr()
    {
        return $this->toastr;
    }

    /**
     * @param $toastr
     * @return $this
     */
    public function setToastr($toastr)
    {
        $this->toastr = $toastr;

        return $this;
    }

    /**
     * @return string
     */
    public function getToastrType()
    {
        return $this->toastrType;
    }

    /**
     * @param $toastrType
     * @return $this
     */
    public function setToastrType($toastrType)
    {
        $this->toastrType = $toastrType;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * @param array $routeParameters
     * @return $this
     */
    public function setRouteParameters(array $routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }
}