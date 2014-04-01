<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use OroCRM\Bundle\ContactBundle\Model\ExtendContact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_contact",
 *      indexes={@ORM\Index(name="contact_name_idx",columns={"first_name", "last_name"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_contact_index",
 *  routeView="orocrm_contact_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-group"
 *      },
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "form"={
 *          "form_type"="orocrm_contact_select"
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class Contact extends ExtendContact implements Taggable, EmailOwnerInterface
{
    /*
     * Fields have to be duplicated here to enable dataaudit and soap transformation only for contact
     */
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $namePrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Soap\ComplexType("string")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Soap\ComplexType("string")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $nameSuffix;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=8, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Soap\ComplexType("date", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $description;

    /**
     * @var Source
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Source")
     * @ORM\JoinColumn(name="source_name", referencedColumnName="name")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $source;

    /**
     * @var Method
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Method")
     * @ORM\JoinColumn(name="method_name", referencedColumnName="name")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $method;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $owner;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_user_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $assignedTo;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="reports_to_contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $reportsTo;

    /**
     * @var string
     *
     * @ORM\Column(name="job_title", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $jobTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $email;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\ContactEmail",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     * @Soap\ComplexType("OroCRM\Bundle\ContactBundle\Entity\ContactEmail[]", nillable=true)
     */
    protected $emails;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\ContactPhone",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     * @Soap\ComplexType("OroCRM\Bundle\ContactBundle\Entity\ContactPhone[]", nillable=true)
     */
    protected $phones;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="skype", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $skype;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $twitter;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $facebook;

    /**
     * @var string
     *
     * @ORM\Column(name="google_plus", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $googlePlus;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $linkedIn;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\ContactAddress",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     */
    protected $addresses;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Group")
     * @ORM\JoinTable(name="orocrm_contact_to_contact_grp",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contact_group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $groups;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", mappedBy="contacts")
     * @ORM\JoinTable(name="orocrm_contact_to_account")
     */
    protected $accounts;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $updatedBy;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    public function __construct()
    {
        parent::__construct();

        $this->groups    = new ArrayCollection();
        $this->accounts  = new ArrayCollection();
        $this->emails    = new ArrayCollection();
        $this->phones    = new ArrayCollection();
        $this->tags      = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();

        if ($this->groups) {
            $this->groups = clone $this->groups;
        }
        if ($this->accounts) {
            $this->accounts = clone $this->accounts;
        }
        if ($this->emails) {
            $this->emails = clone $this->emails;
        }
        if ($this->phones) {
            $this->phones = clone $this->phones;
        }
        if ($this->tags) {
            $this->tags = clone $this->tags;
        }
    }

    /**
     * Get entity class name.
     *
     * @return string
     */
    public function getClass()
    {
        return 'OroCRM\Bundle\ContactBundle\Entity\Contact';
    }

    /**
     * Get name of field contains the primary email address
     *
     * @return string
     */
    public function getPrimaryEmailField()
    {
        // TODO: Return correct field name after refactoring of contact class
        return null;
    }

    /**
     * @param User $assignedTo
     * @return Contact
     */
    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param string $description
     * @return Contact
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Source $source
     * @return Contact
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Method $method
     * @return Contact
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param User $owningUser
     * @return Contact
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Contact $reportsTo
     * @return Contact
     */
    public function setReportsTo($reportsTo)
    {
        $this->reportsTo = $reportsTo;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getReportsTo()
    {
        return $this->reportsTo;
    }

    /**
     * @param string $jobTitle
     * @return Contact
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $fax
     * @return Contact
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $skype
     * @return Contact
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * @param string $facebookUrl
     * @return Contact
     */
    public function setFacebook($facebookUrl)
    {
        $this->facebook = $facebookUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $googlePlusUrl
     * @return Contact
     */
    public function setGooglePlus($googlePlusUrl)
    {
        $this->googlePlus = $googlePlusUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getGooglePlus()
    {
        return $this->googlePlus;
    }

    /**
     * @param string $linkedInUrl
     * @return Contact
     */
    public function setLinkedIn($linkedInUrl)
    {
        $this->linkedIn = $linkedInUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkedIn()
    {
        return $this->linkedIn;
    }

    /**
     * @param string $twitterUrl
     * @return Contact
     */
    public function setTwitter($twitterUrl)
    {
        $this->twitter = $twitterUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @return int
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        if (null === $this->tags) {
            $this->tags = new ArrayCollection();
        }

        return $this->tags;
    }

    /**
     * @param $tags
     * @return Contact
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set emails.
     *
     * This method could not be named setEmails because of bug CRM-253.
     *
     * @param Collection|ContactEmail[] $emails
     * @return Contact
     */
    public function resetEmails($emails)
    {
        $this->emails->clear();

        foreach ($emails as $email) {
            $this->addEmail($email);
        }

        return $this;
    }

    /**
     * Add email
     *
     * @param ContactEmail $email
     * @return Contact
     */
    public function addEmail(ContactEmail $email)
    {
        if (!$this->emails->contains($email)) {
            $this->emails->add($email);
            $email->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove email
     *
     * @param ContactEmail $email
     * @return Contact
     */
    public function removeEmail(ContactEmail $email)
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
        }

        return $this;
    }

    /**
     * Get emails
     *
     * @return Collection|ContactEmail[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Gets primary email if it's available.
     *
     * @return ContactEmail|null
     */
    public function getPrimaryEmail()
    {
        $result = null;

        foreach ($this->getEmails() as $email) {
            if ($email->isPrimary()) {
                $result = $email;
                break;
            }
        }

        return $result;
    }

    /**
     * Set phones.
     *
     * This method could not be named setPhones because of bug CRM-253.
     *
     * @param Collection|ContactPhone[] $phones
     * @return Contact
     */
    public function resetPhones($phones)
    {
        $this->phones->clear();

        foreach ($phones as $phone) {
            $this->addPhone($phone);
        }

        return $this;
    }

    /**
     * Add phone
     *
     * @param ContactPhone $phone
     * @return Contact
     */
    public function addPhone(ContactPhone $phone)
    {
        if (!$this->phones->contains($phone)) {
            $this->phones->add($phone);
            $phone->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove phone
     *
     * @param ContactPhone $phone
     * @return Contact
     */
    public function removePhone(ContactPhone $phone)
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    /**
     * Get phones
     *
     * @return Collection|ContactPhone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Gets primary phone if it's available.
     *
     * @return ContactPhone|null
     */
    public function getPrimaryPhone()
    {
        $result = null;

        foreach ($this->getPhones() as $phone) {
            if ($phone->isPrimary()) {
                $result = $phone;
                break;
            }
        }

        return $result;
    }

    /**
     * Add address
     *
     * @param AbstractAddress $address
     * @return BasePerson
     */
    public function addAddress(AbstractAddress $address)
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setOwner($this);
        }

        return $this;
    }

    /**
     * Gets primary address if it's available.
     *
     * @return ContactAddress|null
     */
    public function getPrimaryAddress()
    {
        $result = null;

        foreach ($this->getAddresses() as $address) {
            if ($address->isPrimary()) {
                $result = $address;
                break;
            }
        }

        return $result;
    }

    /**
     * Gets primary address if it's available
     *
     * @param ContactAddress $address
     * @return Contact
     */
    public function setPrimaryAddress(ContactAddress $address)
    {
        if ($this->hasAddress($address)) {
            $address->setPrimary(true);
            foreach ($this->getAddresses() as $otherAddress) {
                if (!$address->isEqual($otherAddress)) {
                    $otherAddress->setPrimary(false);
                }
            }
        }

        return $this;
    }

    /**
     * Gets address type if it's available.
     *
     * @param ContactAddress $address
     * @param AddressType    $addressType
     * @return Contact
     */
    public function setAddressType(ContactAddress $address, AddressType $addressType)
    {
        if ($this->hasAddress($address)) {
            $address->addType($addressType);
            foreach ($this->getAddresses() as $otherAddress) {
                if (!$address->isEqual($otherAddress)) {
                    $otherAddress->removeType($addressType);
                }
            }
        }

        return $this;
    }

    /**
     * Gets one address that has specified type.
     *
     * @param AddressType $type
     * @return ContactAddress|null
     */
    public function getAddressByType(AddressType $type)
    {
        return $this->getAddressByTypeName($type->getName());
    }

    /**
     * Gets one address that has specified type name.
     *
     * @param string $typeName
     * @return ContactAddress|null
     */
    public function getAddressByTypeName($typeName)
    {
        $result = null;

        foreach ($this->getAddresses() as $address) {
            if ($address->hasTypeWithName($typeName)) {
                $result = $address;
                break;
            }
        }

        return $result;
    }

    /**
     * Get group labels separated with comma.
     *
     * @return string
     */
    public function getGroupLabelsAsString()
    {
        return implode(', ', $this->getGroupLabels());
    }

    /**
     * Get list of group labels
     *
     * @return array
     */
    public function getGroupLabels()
    {
        $result = array();

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $result[] = $group->getLabel();
        }

        return $result;
    }

    /**
     * Gets the groups related to contact
     *
     * @return Group[]|Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add specified group
     *
     * @param Group $group
     * @return Contact
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * Remove specified group
     *
     * @param Group $group
     * @return Contact
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Get accounts collection
     *
     * @return Collection|Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Add specified account
     *
     * @param Account $account
     * @return Contact
     */
    public function addAccount(Account $account)
    {
        if (!$this->getAccounts()->contains($account)) {
            $this->getAccounts()->add($account);
            $account->addContact($this);
        }

        return $this;
    }

    /**
     * Remove specified account
     *
     * @param Account $account
     * @return Contact
     */
    public function removeAccount(Account $account)
    {
        if ($this->getAccounts()->contains($account)) {
            $this->getAccounts()->removeElement($account);
            $account->removeContact($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAccounts()
    {
        return count($this->accounts) > 0;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $createdBy
     * @return Contact
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $updatedBy
     * @return Contact
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $name = $this->getNamePrefix() . ' '
            . $this->getFirstName() . ' '
            . $this->getMiddleName() . ' '
            . $this->getLastName() . ' '
            . $this->getNameSuffix();
        $name = preg_replace('/ +/', ' ', $name);

        return (string) trim($name);
    }
}
