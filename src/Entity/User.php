<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection,
    Symfony\Component\Security\Core\User\UserInterface,
    Doctrine\Common\Collections\Collection,
    Symfony\Component\Validator\Context\ExecutionContextInterface;

use App\Library\BaseEntity,
    App\Entity\School\Request,
    App\Entity\School\RequestAssessor,
    Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class User
 * @package App\Entity
 */
class User extends BaseEntity implements UserInterface, \Serializable
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var Collection
     */
    private $roles;

    /**
     * @var string $salt
     */
    private $salt;

    /**
     * This is the preferred locale of the user
     *
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var \datetime
     */
    private $hashCreatedAt;

    /**
     * @var \MediaBundle\Entity\Media
     */
    private $avatar;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var Collection
     */
    private $followers;

    /**
     * @var Collection
     */
    private $follow;

    /**
     * @var Collection
     */
    private $notifications;

    /**
     * @var Collection
     */
    private $logs;

    /**
     * @var UserSetting
     */
    private $settings;

    /**
     * @var Collection
     */
    private $requests;

    /**
     * @var Collection
     */
    private $assignedRequests;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->salt = md5(uniqid(null, true));
        $this->followers = new ArrayCollection();
        $this->follow = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->locale = 'en';
        $this->requests = new ArrayCollection();
        $this->assignedRequests = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->id) {
            if (empty($this->firstname) && empty($this->lastname)) {
                return $this->username;
            }

            return $this->firstname . ' ' . $this->lastname;
        }

        return 'New User';
    }

    /**
     * Set Id
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->__toString();
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get Roles
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = array();

        foreach ($this->roles as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * Get User Roles
     *
     * @return ArrayCollection
     */
    public function getUserRoles()
    {
        return $this->roles;
    }

    /**
     * Add Role
     *
     * @param Role $role
     *
     * @return $this
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove Role
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Set Roles
     *
     * @param $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * Set User Roles
     *
     * @param $roles
     */
    public function setUserRoles($roles)
    {
        $this->setRoles($roles);
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
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
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $avatar
     * @return $this
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return \MediaBundle\Entity\Media
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Erase Credentials
     */
    public function eraseCredentials()
    {
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->active;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->active
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->active
            ) = unserialize($serialized);
    }

    /**
     * @return bool
     */
    public function isDeletable()
    {
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        return ($this->id != $currentUser->getId()) ?: false;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return \datetime
     */
    public function getHashCreatedAt()
    {
        return $this->hashCreatedAt;
    }

    /**
     * @param $hashCreatedAt
     * @return $this
     */
    public function setHashCreatedAt($hashCreatedAt)
    {
        $this->hashCreatedAt = $hashCreatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Add followers
     *
     * @param User $followers
     * @return User
     */
    public function addFollower(User $followers)
    {
        $this->followers[] = $followers;

        return $this;
    }

    /**
     * Remove followers
     *
     * @param User $followers
     */
    public function removeFollower(User $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add follow
     *
     * @param User $follow
     * @return User
     */
    public function addFollow(User $follow)
    {
        $this->follow[] = $follow;

        return $this;
    }

    /**
     * Remove follow
     *
     * @param User $follow
     */
    public function removeFollow(User $follow)
    {
        $this->follow->removeElement($follow);
    }

    /**
     * Get follow
     *
     * @return Collection
     */
    public function getFollow()
    {
        return $this->follow;
    }

    /**
     * Username must contain alphanumeric only
     *
     * @param ExecutionContextInterface $context
     */
    public function usernameValid(ExecutionContextInterface $context)
    {
        if (false !== strpos($this->username, ' ')) {
            $context
                ->buildViolation('Spaces and symbols are not allowed.')
                ->atPath('username')
                ->addViolation();
        } elseif (false === ctype_alnum($this->username)) {
            $context
                ->buildViolation('Username must be alphanumeric.')
                ->atPath('username')
                ->addViolation();
        }
    }

    /**
     * Add notifications
     *
     * @param UserNotification $notifications
     * @return User
     */
    public function addNotification(UserNotification $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param UserNotification $notifications
     */
    public function removeNotification(UserNotification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add logs
     *
     * @param Log $logs
     * @return User
     */
    public function addLog(Log $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param Log $logs
     */
    public function removeLog(Log $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set settings
     *
     * @param UserSetting $settings
     * @return User
     */
    public function setSettings(UserSetting $settings = null)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return UserSetting
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param Request $publishingRequest
     * @return $this
     */
    public function addRequests(Request $publishingRequest)
    {
        $this->requests->add($publishingRequest);

        return $this;
    }

    /**
     * @param Request $publishingRequest
     * @return $this
     */
    public function removeRequests(Request $publishingRequest)
    {
        $this->requests->removeElement($publishingRequest);

        return $this;
    }

    /**
     * @param ArrayCollection $requests
     * @return $this
     */
    public function setRequests(ArrayCollection $requests)
    {
        $this->requests = $requests;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getAssignedRequests()
    {
        return $this->assignedRequests;
    }

    /**
     * @param RequestAssessor $assignedRequest
     * @return $this
     */
    public function addAssignedRequest(RequestAssessor $assignedRequest)
    {
        $this->assignedRequests->add($assignedRequest);

        return $this;
    }

    /**
     * @param RequestAssessor $assignedRequest
     * @return $this
     */
    public function removeAssignedRequest(RequestAssessor $assignedRequest)
    {
        $this->assignedRequests->removeElement($assignedRequest);

        return $this;
    }

    /**
     * @param ArrayCollection $assignedRequests
     * @return $this
     */
    public function setAssignedRequests(ArrayCollection $assignedRequests)
    {
        $this->assignedRequests = $assignedRequests;

        return $this;
    }
}