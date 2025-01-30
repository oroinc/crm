<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactBundle_Entity_Contact;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePerson;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represent contact information (possibly a person or a business).
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @mixin OroContactBundle_Entity_Contact
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'orocrm_contact')]
#[ORM\Index(columns: ['last_name', 'first_name', 'id'], name: 'contact_name_idx')]
#[ORM\Index(columns: ['first_name'], name: 'contact_first_name_idx')]
#[ORM\Index(columns: ['updatedAt'], name: 'contact_updated_at_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_contact_index',
    routeView: 'oro_contact_view',
    defaultValues: [
        'entity' => [
            'icon' => 'fa-users',
            'contact_information' => [
                'email' => [['fieldName' => 'primaryEmail']],
                'phone' => [['fieldName' => 'primaryPhone']]
            ]
        ],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'user_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'account_management'],
        'form' => ['form_type' => ContactSelectType::class, 'grid_name' => 'contacts-select-grid'],
        'dataaudit' => ['auditable' => true],
        'grid' => ['default' => 'contacts-grid', 'context' => 'contacts-for-context-grid'],
        'tag' => ['enabled' => true],
        'merge' => ['enable' => true]
    ]
)]
class Contact extends BasePerson implements EmailOwnerInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    /*
     * Fields have to be duplicated here to enable dataaudit only for contact
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 10]])]
    protected ?int $id = null;

    #[ORM\Column(name: 'name_prefix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 20],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $namePrefix = null;

    #[ORM\Column(name: 'first_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['identity' => true, 'order' => 30],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'middle_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 40],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $middleName = null;

    #[ORM\Column(name: 'last_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['identity' => true, 'order' => 50],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $lastName = null;

    #[ORM\Column(name: 'name_suffix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 60],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $nameSuffix = null;

    #[ORM\Column(name: 'gender', type: Types::STRING, length: 8, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 70],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $gender = null;

    #[ORM\Column(name: 'birthday', type: Types::DATE_MUTABLE, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 160],
            'merge' => ['display' => true]
        ]
    )]
    protected ?\DateTimeInterface $birthday = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 80],
            'merge' => ['display' => true, 'autoescape' => false]
        ]
    )]
    protected ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Source::class)]
    #[ORM\JoinColumn(name: 'source_name', referencedColumnName: 'name')]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 170],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Source $source = null;

    #[ORM\ManyToOne(targetEntity: Method::class)]
    #[ORM\JoinColumn(name: 'method_name', referencedColumnName: 'name')]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 180],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Method $method = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 190, 'short' => true],
            'merge' => ['display' => true]
        ]
    )]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'assigned_to_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 200, 'short' => true],
            'merge' => ['display' => true]
        ]
    )]
    protected ?User $assignedTo = null;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'reports_to_contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['excluded' => true],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Contact $reportsTo = null;

    #[ORM\Column(name: 'job_title', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 90],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $jobTitle = null;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'entity' => ['contact_information' => 'email']]
    )]
    protected ?string $email = null;

    /**
     * @var Collection<int, ContactEmail>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ContactEmail::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(
        defaultValues: [
            'importexport' => ['order' => 210],
            'dataaudit' => ['auditable' => true],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Collection $emails = null;

    /**
     * @var Collection<int, ContactPhone>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ContactPhone::class, cascade: ['all'], orphanRemoval: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 220],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Collection $phones = null;

    #[ORM\Column(name: 'fax', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 100],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $fax = null;

    #[ORM\Column(name: 'skype', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 110],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $skype = null;

    #[ORM\Column(name: 'twitter', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 120],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $twitter = null;

    #[ORM\Column(name: 'facebook', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 130],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $facebook = null;

    #[ORM\Column(name: 'google_plus', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 140],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $googlePlus = null;

    #[ORM\Column(name: 'linkedin', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(
        defaultValues: [
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 150],
            'merge' => ['display' => true]
        ]
    )]
    protected ?string $linkedIn = null;

    /**
     * @var Collection<int, ContactAddress>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ContactAddress::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(
        defaultValues: [
            'importexport' => ['full' => true, 'order' => 250],
            'dataaudit' => ['auditable' => true],
            'merge' => ['display' => true]
        ]
    )]
    protected ?Collection $addresses = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class)]
    #[ORM\JoinTable(name: 'orocrm_contact_to_contact_grp')]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'contact_group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(
        defaultValues: ['importexport' => ['order' => 230, 'short' => true], 'merge' => ['display' => true]]
    )]
    protected ?Collection $groups = null;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\ManyToMany(targetEntity: Account::class, mappedBy: 'contacts')]
    #[ORM\JoinTable(name: 'orocrm_account_to_contact')]
    #[ConfigField(
        defaultValues: ['importexport' => ['order' => 240, 'short' => true], 'merge' => ['display' => true]]
    )]
    protected ?Collection $accounts = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'updated_by_user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?User $updatedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.created_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.updated_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\OneToMany(mappedBy: 'defaultContact', targetEntity: Account::class, cascade: ['persist'])]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 240]])]
    protected ?Collection $defaultInAccounts = null;

    public function __construct()
    {
        parent::__construct();

        $this->groups   = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->emails   = new ArrayCollection();
        $this->phones   = new ArrayCollection();
        $this->defaultInAccounts = new ArrayCollection();
    }

    #[\Override]
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
        if ($this->defaultInAccounts) {
            $this->defaultInAccounts = clone $this->defaultInAccounts;
        }
        $this->cloneExtendEntityStorage();
    }

    /**
     * Get names of fields contain email addresses
     *
     * @return string[]|null
     */
    #[\Override]
    public function getEmailFields()
    {
        return null;
    }

    /**
     * @param User $assignedTo
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     *
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
     * Set emails.
     *
     * This method could not be named setEmails because of bug CRM-253.
     *
     * @param Collection|ContactEmail[] $emails
     *
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
     *
     * @return Contact
     */
    public function addEmail(ContactEmail $email)
    {
        if (!$this->emails->contains($email)) {
            //don't allow more than one primary email
            if ($email->isPrimary() && $this->getPrimaryEmail()) {
                $email->setPrimary(false);
            }

            $this->emails->add($email);
            $email->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove email
     *
     * @param ContactEmail $email
     *
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

    #[\Override]
    public function getEmail()
    {
        $primaryEmail = $this->getPrimaryEmail();
        if (!$primaryEmail) {
            return null;
        }

        return $primaryEmail->getEmail();
    }

    /**
     * @param ContactEmail $email
     * @return bool
     */
    public function hasEmail(ContactEmail $email)
    {
        return $this->getEmails()->contains($email);
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
     * @param ContactEmail $email
     * @return Contact
     */
    public function setPrimaryEmail(ContactEmail $email)
    {
        if ($this->hasEmail($email)) {
            $email->setPrimary(true);
            foreach ($this->getEmails() as $otherEmail) {
                if (!$email->isEqual($otherEmail)) {
                    $otherEmail->setPrimary(false);
                }
            }
        }

        return $this;
    }

    /**
     * Set phones.
     *
     * This method could not be named setPhones because of bug CRM-253.
     *
     * @param Collection|ContactPhone[] $phones
     *
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
     *
     * @return Contact
     */
    public function addPhone(ContactPhone $phone)
    {
        if (!$this->phones->contains($phone)) {
            //don't allow more than one primary phone
            if ($phone->isPrimary() && $this->getPrimaryPhone()) {
                $phone->setPrimary(false);
            }

            $this->phones->add($phone);
            $phone->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove phone
     *
     * @param ContactPhone $phone
     *
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
     * @param ContactPhone $phone
     * @return bool
     */
    public function hasPhone(ContactPhone $phone)
    {
        return $this->getPhones()->contains($phone);
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
     * @param ContactPhone $phone
     * @return Contact
     */
    public function setPrimaryPhone(ContactPhone $phone)
    {
        if ($this->hasPhone($phone)) {
            $phone->setPrimary(true);
            foreach ($this->getPhones() as $otherPhone) {
                if (!$phone->isEqual($otherPhone)) {
                    $otherPhone->setPrimary(false);
                }
            }
        }

        return $this;
    }

    /**
     * Add address
     *
     * @param AbstractAddress $address
     *
     * @return BasePerson
     */
    #[\Override]
    public function addAddress(AbstractAddress $address)
    {
        if (!$address instanceof ContactAddress) {
            throw new \InvalidArgumentException("Address must be instance of ContactAddress");
        }

        /** @var ContactAddress $address */
        if (!$this->addresses->contains($address)) {
            //don't allow more than one primary address
            if ($address->isPrimary() && $this->getPrimaryAddress()) {
                $address->setPrimary(false);
            }

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

        /** @var ContactAddress $address */
        foreach ($this->getAddresses() as $address) {
            if ($address->isPrimary()) {
                $result = $address;
                break;
            }
        }

        return $result;
    }

    /**
     * @param ContactAddress $address
     *
     * @return Contact
     */
    public function setPrimaryAddress(ContactAddress $address)
    {
        if ($this->hasAddress($address)) {
            $address->setPrimary(true);
            /** @var ContactAddress $otherAddress */
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
     *
     * @return Contact
     */
    public function setAddressType(ContactAddress $address, AddressType $addressType)
    {
        if ($this->hasAddress($address)) {
            $address->addType($addressType);
            /** @var ContactAddress $otherAddress */
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
     *
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
     *
     * @return ContactAddress|null
     */
    public function getAddressByTypeName($typeName)
    {
        $result = null;

        /** @var ContactAddress $address */
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
        $result = [];

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
     *
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
     *
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
     *
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
     *
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
     * @param User $createdBy
     *
     * @return Contact
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $updatedBy
     *
     * @return Contact
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        $name = $this->getNamePrefix() . ' '
            . $this->getFirstName() . ' '
            . $this->getMiddleName() . ' '
            . $this->getLastName() . ' '
            . $this->getNameSuffix();
        $name = preg_replace('/ +/', ' ', $name);

        return (string)trim($name);
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return Contact
     */
    public function setOrganization(?Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function addDefaultInAccount(Account $account)
    {
        if (!$this->defaultInAccounts->contains($account)) {
            $this->defaultInAccounts->add($account);
            $account->setDefaultContact($this);
        }

        return $this;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function removeDefaultInAccount(Account $account)
    {
        $this->defaultInAccounts->removeElement($account);
        if ($account->getDefaultContact() === $this) {
            $account->setDefaultContact(null);
        }

        return $this;
    }

    /**
     * @return Account[]|Collection
     */
    public function getDefaultInAccounts()
    {
        return $this->defaultInAccounts;
    }
}
