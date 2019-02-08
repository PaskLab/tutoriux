<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Log
 * @package App\Entity
 */
class Log
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
     * @var string
     */
    private $followersRoute;

    /**
     * @var array
     */
    private $followersRouteParameters;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var User
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->public = false;
        $this->parameters = [];
        $this->routeParameters = [];
        $this->followersRouteParameters = [];
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
     * @return Log
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
     * @return Log
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
     * Set icon
     *
     * @param string $icon
     * @return Log
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set iconColor
     *
     * @param string $iconColor
     * @return Log
     */
    public function setIconColor($iconColor)
    {
        $this->iconColor = $iconColor;

        return $this;
    }

    /**
     * Get iconColor
     *
     * @return string 
     */
    public function getIconColor()
    {
        return $this->iconColor;
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

    /**
     * @return string
     */
    public function getFollowersRoute()
    {
        return $this->followersRoute;
    }

    /**
     * @param $followersRoute
     * @return $this
     */
    public function setFollowersRoute($followersRoute)
    {
        $this->followersRoute = $followersRoute;

        return $this;
    }

    /**
     * @return array
     */
    public function getFollowersRouteParameters()
    {
        return $this->followersRouteParameters;
    }

    /**
     * @param array $followersRouteParameters
     * @return $this
     */
    public function setFollowersRouteParameters(array $followersRouteParameters)
    {
        $this->followersRouteParameters = $followersRouteParameters;

        return $this;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return Log
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Log
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
}