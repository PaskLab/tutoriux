<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\Role as SymfonyRole;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Role
 * @package App\Entity
 */
class Role extends SymfonyRole implements \Serializable
{
    use TutoriuxORMBehaviors\Translatable\Translatable;
    use TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string $role
     */
    private $role;

    /**
     * @var ArrayCollection
     */
    private $users;

    /**
     * @var ArrayCollection
     */
    private $sections;

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
     * Set id
     * 
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New role';
        }

        if ($name = $this->getName()) {
            return $name;
        }

        // No translation found in the current locale
        return '';
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    /**
     * Get Role
     *
     * @return null|string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        
        return $this;
    }

    /**
     * Add users
     *
     * @param User $users
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add sections
     *
     * @param  Section $sections
     * @return Role
     */
    public function addSection(Section $sections)
    {
        $this->sections[] = $sections;

        return $this;
    }

    /**
     * @param ArrayCollection $sections
     * @return $this
     */
    public function setSections(ArrayCollection $sections)
    {
        $this->sections = $sections;
        
        return $this;
    }

    /**
     * Remove sections
     *
     * @param Section $sections
     */
    public function removeSection(Section $sections)
    {
        $this->sections->removeElement($sections);
    }

    /**
     * Get sections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get the backend route
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getRoute($suffix = 'edit'): string
    {
        return 'system_backend_role_' . $suffix;
    }

    /**
     * Get params for the backend route
     *
     * @param array $params Additional parameters
     *
     * @return array
     */
    public function getRouteParams($params = array()): array
    {
        $defaults = array(
            'id' => $this->id ? $this->id : 0
        );

        $params = array_merge($defaults, $params);

        return $params;
    }

    /**
     * Used to serialize the User in the Session
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->role
        ));
    }

    /**
     * Used to unserialize the User from the Session
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->role
        ) = unserialize($serialized);
    }
}
