<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Oro\Bundle\UserBundle\Entity\Status;
use Oro\Bundle\UserBundle\Entity\Email;

use DateTime;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="oro_user")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class User extends AbstractEntityFlexible implements AdvancedUserInterface, \Serializable
{
    const ROLE_DEFAULT   = 'ROLE_USER';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Gedmo\Versioned
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Gedmo\Versioned
     */
    protected $email;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Gedmo\Versioned
     */
    protected $firstName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Gedmo\Versioned
     */
    protected $lastName;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="birthday", type="datetime", nullable=true)
     * @Soap\ComplexType("dateTime", nillable=true)
     * @Type("dateTime")
     * @Gedmo\Versioned
     */
    protected $birthday;

    /**
     * Image filename
     *
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Exclude
     */
    protected $image;

    /**
     * Image filename
     *
     * @var UploadedFile
     *
     * @Exclude
     */
    protected $imageFile;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @Soap\ComplexType("boolean")
     * @Type("boolean")
     * @Gedmo\Versioned
     */
    protected $enabled = true;

    /**
     * The salt to use for hashing
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Exclude
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @Exclude
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     * @Soap\ComplexType("string", nillable=true)
     * @Exclude
     */
    protected $plainPassword;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     * @Exclude
     */
    protected $confirmationToken;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="password_requested", type="datetime", nullable=true)
     * @Exclude
     */
    protected $passwordRequestedAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     * @Soap\ComplexType("dateTime", nillable=true)
     * @Type("dateTime")
     */
    protected $lastLogin;

    /**
     * @var int
     *
     * @ORM\Column(name="login_count", type="integer", options={"default"=0, "unsigned"=true})
     * @Exclude
     */
    protected $loginCount;

    /**
     * Set name formatting using "%first%" and "%last%" placeholders
     *
     * @var string
     *
     * @Exclude
     */
    protected $nameFormat;

    /**
     * @var Role[]
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]", nillable=true)
     * @Exclude
     */
    protected $roles;

    /**
     * @var Group[]
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="oro_user_access_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]", nillable=true)
     * @Exclude
     */
    protected $groups;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="UserValue", mappedBy="entity", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * @ORM\OneToOne(
     *  targetEntity="UserApi", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY"
     * )
     */
    protected $api;

    /**
     * @var Status[]
     *
     * @ORM\OneToMany(targetEntity="Status", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $statuses;

    /**
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $currentStatus;

    /**
     * @var Email[]
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    protected $emails;

    public function __construct()
    {
        parent::__construct();

        $this->salt     = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles    = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->emails   = new ArrayCollection();
    }

    /**
     * Serializes the user.
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->password,
                $this->salt,
                $this->username,
                $this->enabled,
                $this->confirmationToken,
                $this->id,
            )
        );
    }

    /**
     * Unserializes the user
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->confirmationToken,
            $this->id
        ) = unserialize($serialized);
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstName;
    }

    /**
     * Return last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * Return full name according to name format
     *
     * @see User::setNameFormat()
     * @param  string $format [optional]
     * @return string
     */
    public function getFullname($format = '')
    {
        return str_replace(
            array('%first%', '%last%'),
            array($this->getFirstname(), $this->getLastname()),
            $format ? $format : $this->getNameFormat()
        );
    }

    /**
     * Return birthday
     *
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Return image filename
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return image file
     *
     * @return UploadedFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Gets the last login time.
     *
     * @return DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Gets login count number.
     *
     * @return int
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * Get full name format. Defaults to "%first% %last%".
     *
     * @return string
     */
    public function getNameFormat()
    {
        return $this->nameFormat ?  $this->nameFormat : '%first% %last%';
    }

    /**
     * Get user created date/time
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get user last update date/time
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * @return UserApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return $this->isEnabled();
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     *
     * @param  string $username New username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param  string $email New email value
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param  string $firstName [optional] New first name value. Null by default.
     * @return User
     */
    public function setFirstname($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     * @return User
     */
    public function setLastname($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     *
     * @param  DateTime $birthday [optional] New birthday value. Null by default.
     * @return User
     */
    public function setBirthday(DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @param  string $image [optional] New image file name. Null by default.
     * @return User
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param  UploadedFile $imageFile
     * @return User
     */
    public function setImageFile(UploadedFile $imageFile)
    {
        $this->imageFile = $imageFile;
        $this->updated   = new DateTime(); // this will trigger PreUpdate callback even if only image has been changed

        return $this;
    }

    /**
     * @param  bool $enabled User state
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;

        return $this;
    }

    /**
     * @param  string $password New encoded password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param  string $password New password as plain string
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @param  string $confirmationToken New confirmation token
     * @return User
     */
    public function setConfirmationToken($token)
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * @param  DateTime $time [optional] New password request time. Null by default.
     * @return User
     */
    public function setPasswordRequestedAt(DateTime $time = null)
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }

    /**
     * @param  DateTime $time New login time
     * @return User
     */
    public function setLastLogin(DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * @param  int  $count New login count value
     * @return User
     */
    public function setLoginCount($count)
    {
        $this->loginCount = $count;

        return $this;
    }

    /**
     * Set new format for a full name display. Use %first% and %last% placeholders, for example: "%last%, %first%".
     *
     * @param  string $format New format string
     * @return User
     */
    public function setNameFormat($format)
    {
        $this->nameFormat = $format;

        return $this;
    }

    /**
     * @param  UserApi $api
     * @return User
     */
    public function setApi(UserApi $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles->toArray();

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles()->toArray());
        }

        return array_unique($roles);
    }

    /**
     * Returns the true ArrayCollection of Roles.
     *
     * @return ArrayCollection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string    $role Role name
     * @return Role|null
     */
    public function getRole($role)
    {
        foreach ($this->getRoles() as $item) {
            if ($role == $item->getRole()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Never use this to check if this user has access to anything!
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param  Role|string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        $role = $role instanceof Role ? $role->getRole() : $role;
        return (bool)$this->getRole($role);
    }

    /**
     * Adds a Role to the ArrayCollection.
     * Can't type hint due to interface so throws RuntimeException.
     *
     * @param  Role              $role
     * @return User
     * @throws \RuntimeException
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Pass a string, remove the Role object from collection
     *
     * @param Role|string $role
     */
    public function removeRole($role)
    {
        $role = $role instanceof Role ? $role->getRole() : $role;
        $item = $this->getRole($role);

        if ($item) {
            $this->roles->removeElement($item);
        }
    }

    /**
     * Pass an array of Role objects and re-set roles collection with new Roles.
     * Type hinted array due to interface.
     *
     * @param  array $roles Array of Role objects
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles->clear();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Directly set the ArrayCollection of Roles.
     *
     * @param  ArrayCollection $collection
     * @return User
     */
    public function setRolesCollection($collection)
    {
        $this->roles = $collection;

        return $this;
    }

    /**
     * Gets the groups granted to the user
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames()
    {
        $names = array();

        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     *
     * @param bool $absolute [optional] Return absolute (true) or relative to web dir (false) path to image. False
     *                        by default
     * @return string|null
     */
    public function getImagePath($absolute = false)
    {
        return null === $this->image
            ? null
            : ($absolute
                ? $this->getUploadRootDir() . '/' . $this->image
                : $this->getUploadDir() . '/' . $this->image
            );
    }

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken()
    {
        return base_convert(bin2hex(hash('sha256', uniqid(mt_rand(), true), true)), 16, 36);
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created    =
        $this->updated    = new DateTime();
        $this->loginCount = 0;

        $this->preUpload();
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated    = new DateTime();
        $this->preUpload();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->imageFile) {
            return;
        }

        $dir = $this->getUploadRootDir();

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->imageFile->move($dir, $this->image);

        unset($this->imageFile);
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($file = $this->getImagePath(true)) {
            unlink($file);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Get User Statuses
     *
     * @return Status[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Add Status to User
     *
     * @param  Status $status
     * @return User
     */
    public function addStatus(Status $status)
    {
        $this->statuses[] = $status;

        return $this;
    }

    /**
     * Get Current Status
     *
     * @return Status
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * Set User Current Status
     *
     * @param  Status $status
     * @return User
     */
    public function setCurrentStatus(Status $status = null)
    {
        $this->currentStatus = $status;

        return $this;
    }

    /**
     * Get User Emails
     *
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add Email to User
     *
     * @param  Email $email
     * @return User
     */
    public function addEmail(Email $email)
    {
        $this->emails[] = $email;

        $email->setUser($this);

        return $this;
    }

    /**
     * Delete Email from User
     *
     * @param  Email $email
     * @return User
     */
    public function removeEmail(Email $email)
    {
        $this->emails->removeElement($email);

        return $this;
    }

    /**
     * Get the absolute directory path where uploaded avatars should be saved
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__ . '/../../../../../web/' . $this->getUploadDir();
    }

    /**
     * Get the relative directory path to user avatar
     *
     * @return string
     */
    protected function getUploadDir()
    {
        $suffix = $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m') : date('Y-m');

        return 'uploads'
            . DIRECTORY_SEPARATOR . 'users'
            . DIRECTORY_SEPARATOR . $suffix;
    }

    protected function preUpload()
    {
        if (null !== $this->imageFile) {
            $filename = sha1(uniqid(mt_rand(), true));

            $this->image = $filename . '.' . $this->imageFile->guessExtension();
        }
    }
}
