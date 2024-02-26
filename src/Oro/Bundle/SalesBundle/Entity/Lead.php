<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_Lead;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;
use Oro\Bundle\SalesBundle\Form\Type\LeadSelectType;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity holds information about lead
 *
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @method AbstractEnumValue getStatus()
 * @method Opportunity setStatus(AbstractEnumValue $status)
 * @mixin OroSalesBundle_Entity_Lead
 */
#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\Table(name: 'orocrm_sales_lead')]
#[ORM\Index(columns: ['createdAt', 'id'], name: 'lead_created_idx')]
#[ORM\Index(columns: ['updatedAt'], name: 'lead_updated_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_sales_lead_index',
    routeView: 'oro_sales_lead_view',
    defaultValues: [
        'entity' => [
            'icon' => 'fa-phone',
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
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'sales_data'],
        'form' => ['form_type' => LeadSelectType::class, 'grid_name' => 'sales-lead-grid'],
        'dataaudit' => ['auditable' => true],
        'grid' => ['default' => 'sales-lead-grid', 'context' => 'sales-lead-for-context-grid'],
        'tag' => ['enabled' => true, 'enableDefaultRendering' => false]
    ]
)]
class Lead implements
    FullNameInterface,
    EmailHolderInterface,
    EmailOwnerInterface,
    ExtendEntityInterface
{
    use ExtendEntityTrait;

    const INTERNAL_STATUS_CODE = 'lead_status';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 0]])]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 150, 'short' => true]]
    )]
    protected ?Contact $contact = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 20, 'identity' => true]]
    )]
    protected ?string $name = null;

    #[ORM\Column(name: 'name_prefix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 30]])]
    protected ?string $namePrefix = null;

    #[ORM\Column(name: 'first_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 40]])]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'middle_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 50]])]
    protected ?string $middleName = null;

    #[ORM\Column(name: 'last_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 60]])]
    protected ?string $lastName = null;

    #[ORM\Column(name: 'name_suffix', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 70]])]
    protected ?string $nameSuffix = null;

    #[ORM\Column(name: 'job_title', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 80]])]
    protected ?string $jobTitle = null;

    /**
     * @var Collection<int, LeadPhone>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: LeadPhone::class, cascade: ['all'], orphanRemoval: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 220]])]
    protected ?Collection $phones = null;

    /**
     * @var Collection<int, LeadEmail>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: LeadEmail::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 210], 'dataaudit' => ['auditable' => true]])]
    protected ?Collection $emails = null;

    #[ORM\Column(name: 'company_name', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 110]])]
    protected ?string $companyName = null;

    #[ORM\Column(name: 'website', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 120]])]
    protected ?string $website = null;

    #[ORM\Column(name: 'number_of_employees', type: Types::INTEGER, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 130]])]
    protected ?int $numberOfEmployees = null;

    #[ORM\Column(name: 'industry', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 140]])]
    protected ?string $industry = null;

    /**
     * @var Collection<int, LeadAddress>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: LeadAddress::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(
        defaultValues: ['importexport' => ['full' => true, 'order' => 170], 'dataaudit' => ['auditable' => true]]
    )]
    protected ?Collection $addresses = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.created_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.updated_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 180, 'short' => true]]
    )]
    protected ?User $owner = null;

    /**
     * @var Collection<int, Opportunity>
     */
    #[ORM\OneToMany(mappedBy: 'lead', targetEntity: Opportunity::class)]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 190, 'short' => true]])]
    protected ?Collection $opportunities = null;

    #[ORM\Column(name: 'notes', type: Types::TEXT, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 140]])]
    protected ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    #[ORM\Column(name: 'twitter', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 175]])]
    protected ?string $twitter = null;

    #[ORM\Column(name: 'linkedin', type: Types::STRING, length: 255, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 180]])]
    protected ?string $linkedIn = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_association_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true]])]
    protected ?Customer $customerAssociation = null;

    public function __construct()
    {
        $this->opportunities = new ArrayCollection();
        $this->phones   = new ArrayCollection();
        $this->emails   = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailFields()
    {
        return null;
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
     * Set topic
     *
     * @param string $name
     *
     * @return Lead
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get topic
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $namePrefix
     *
     * @return Lead
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * Set first name
     *
     * @param string $firstName
     *
     * @return Lead
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     *
     * @return Lead
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Set last name
     *
     * @param string $lastName
     *
     * @return Lead
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $nameSuffix
     *
     * @return Lead
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * Set job title
     *
     * @param string $jobTitle
     *
     * @return Lead
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Get job title
     *
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * Set company name
     *
     * @param string $companyName
     *
     * @return Lead
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get company name
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Lead
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set number of employees
     *
     * @param integer $numberOfEmployees
     *
     * @return Lead
     */
    public function setNumberOfEmployees($numberOfEmployees)
    {
        $this->numberOfEmployees = $numberOfEmployees;

        return $this;
    }

    /**
     * Get number of employees
     *
     * @return integer
     */
    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    /**
     * Set industry
     *
     * @param string $industry
     *
     * @return Lead
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * Get industry
     *
     * @return string
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * Add address
     *
     * @param AbstractAddress $address
     *
     * @return Lead
     */
    public function addAddress(AbstractAddress $address)
    {
        /** @var LeadAddress $address */
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setOwner($this);
        }

        return $this;
    }

    /**
     * Gets primary address if it's available.
     *
     * @return LeadAddress|null
     */
    public function getPrimaryAddress()
    {
        $result = null;

        /** @var LeadAddress $address */
        foreach ($this->getAddresses() as $address) {
            if ($address->isPrimary()) {
                $result = $address;
                break;
            }
        }

        return $result;
    }

    /**
     * @param LeadAddress $address
     *
     * @return Lead
     */
    public function setPrimaryAddress(LeadAddress $address)
    {
        if ($this->containsAddress($address)) {
            $address->setPrimary(true);
            /** @var LeadAddress $otherAddress */
            foreach ($this->getAddresses() as $otherAddress) {
                if (!$address->isEqual($otherAddress)) {
                    $otherAddress->setPrimary(false);
                }
            }
        }

        return $this;
    }

    /**
     * Get addresses
     *
     * @return Collection|AbstractAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AbstractAddress $address
     * @return bool
     */
    public function containsAddress(AbstractAddress $address)
    {
        return $this->getAddresses()->contains($address);
    }

    /**
     * Remove address
     *
     * @param AbstractAddress $address
     * @return Lead
     */
    public function removeAddress(AbstractAddress $address)
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     *
     * @return Lead
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Get contact created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $created
     *
     * @return Lead
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get lead last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updated
     *
     * @return Lead
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function beforeUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     *
     * @return Lead
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * Get opportunities
     *
     * @return ArrayCollection
     */
    public function getOpportunities()
    {
        return $this->opportunities;
    }

    /**
     * Add opportunity
     *
     * @param Opportunity $opportunity
     *
     * @return Lead
     */
    public function addOpportunity(Opportunity $opportunity)
    {
        if (!$this->opportunities->contains($opportunity)) {
            $opportunity->setLead($this);
            $this->opportunities->add($opportunity);
        }

        return $this;
    }

    /**
     * @param Opportunity $opportunity
     *
     * @return Lead
     */
    public function removeOpportunity(Opportunity $opportunity)
    {
        if ($this->opportunities->contains($opportunity)) {
            $this->opportunities->removeElement($opportunity);
            $opportunity->setLead(null);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return Lead
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return Lead
     */
    public function setOrganization(Organization $organization = null)
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
     * Set phones.
     *
     * This method could not be named setPhones because of bug CRM-253.
     *
     * @param Collection|LeadPhone[] $phones
     *
     * @return Lead
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
     * @param LeadPhone $phone
     *
     * @return Lead
     */
    public function addPhone(LeadPhone $phone)
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
     * @param LeadPhone $phone
     *
     * @return Lead
     */
    public function removePhone(LeadPhone $phone)
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    /**
     * Get phones
     *
     * @return Collection|LeadPhone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param LeadPhone $phone
     *
     * @return bool
     */
    public function hasPhone(LeadPhone $phone)
    {
        return $this->getPhones()->contains($phone);
    }

    /**
     * Gets primary phone if it's available.
     *
     * @return LeadPhone|null
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
     * @param LeadPhone $phone
     *
     * @return Lead
     */
    public function setPrimaryPhone(LeadPhone $phone)
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
     * Set emails.
     **
     * @param Collection|LeadEmail[] $emails
     *
     * @return Lead
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
     * @param LeadEmail $email
     *
     * @return Lead
     */
    public function addEmail(LeadEmail $email)
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
     * @param LeadEmail $email
     *
     * @return Lead
     */
    public function removeEmail(LeadEmail $email)
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
        }

        return $this;
    }

    /**
     * Get emails
     *
     * @return Collection|LeadEmail[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        $primaryEmail = $this->getPrimaryEmail();
        if (!$primaryEmail) {
            return null;
        }

        return $primaryEmail->getEmail();
    }

    /**
     * @param LeadEmail $email
     * @return bool
     */
    public function hasEmail(LeadEmail $email)
    {
        return $this->getEmails()->contains($email);
    }

    /**
     * Gets primary email if it's available.
     *
     * @return LeadEmail|null
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
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return string
     */
    public function getLinkedIn()
    {
        return $this->linkedIn;
    }

    /**
     * @param string $linkedIn
     */
    public function setLinkedIn($linkedIn)
    {
        $this->linkedIn = $linkedIn;
    }

    /**
     * @param Customer|null $customer
     *
     * @return $this
     */
    public function setCustomerAssociation(Customer $customer = null)
    {
        $this->customerAssociation = $customer;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomerAssociation()
    {
        return $this->customerAssociation;
    }
}
