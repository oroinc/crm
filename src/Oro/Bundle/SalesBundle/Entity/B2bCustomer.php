<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_B2bCustomer;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\ChannelBundle\Model\ChannelEntityTrait;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity holds information of Business Customer.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @mixin OroSalesBundle_Entity_B2bCustomer
 */
#[ORM\Entity(repositoryClass: B2bCustomerRepository::class)]
#[ORM\Table(name: 'orocrm_sales_b2bcustomer')]
#[ORM\Index(columns: ['name', 'id'], name: 'orocrm_b2bcustomer_name_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_sales_b2bcustomer_index',
    routeCreate: 'oro_sales_b2bcustomer_create',
    routeView: 'oro_sales_b2bcustomer_view',
    defaultValues: [
        'entity' => [
            'icon' => 'fa-user-md',
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
        'dataaudit' => ['auditable' => true],
        'form' => ['form_type' => B2bCustomerSelectType::class],
        'grid' => ['default' => 'oro-sales-b2bcustomers-grid', 'context' => 'oro-sales-b2bcustomers-for-context-grid'],
        'tag' => ['enabled' => true]
    ]
)]
class B2bCustomer implements
    ChannelAwareInterface,
    ExtendEntityInterface
{
    use ChannelEntityTrait;
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 0]])]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['identity' => true, 'order' => 10]]
    )]
    protected ?string $name = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'lifetime', type: 'money', nullable: true)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => false], 'importexport' => ['full' => true, 'order' => 15]]
    )]
    protected $lifetime = 0;

    #[ORM\ManyToOne(targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'shipping_address_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true, 'order' => 20]])]
    protected ?Address $shippingAddress = null;

    #[ORM\ManyToOne(targetEntity: Address::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'billing_address_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true, 'order' => 30]])]
    protected ?Address $billingAddress = null;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 40, 'short' => true]]
    )]
    protected ?Account $account = null;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 50, 'short' => true]]
    )]
    protected ?Contact $contact = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['order' => 70, 'short' => true]]
    )]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

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

    /**
     * @var Collection<int, B2bCustomerPhone>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: B2bCustomerPhone::class, cascade: ['all'], orphanRemoval: true)]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 80], 'dataaudit' => ['auditable' => true]])]
    protected ?Collection $phones = null;

    /**
     * @var Collection<int, B2bCustomerEmail>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: B2bCustomerEmail::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 90], 'dataaudit' => ['auditable' => true]])]
    protected ?Collection $emails = null;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->emails = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param float $lifetime
     *
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address|null $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(Address $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address|null $billingAddress
     *
     * @return $this
     */
    public function setBillingAddress(Address $billingAddress = null)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account|null $account
     *
     * @return $this
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

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
     * @param Contact|null $contact
     *
     * @return $this
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     *
     * @return $this
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return B2bCustomer
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
     * @param Collection|B2bCustomerPhone[] $phones
     *
     * @return B2bCustomer
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
     * @param B2bCustomerPhone $phone
     *
     * @return B2bCustomer
     */
    public function addPhone(B2bCustomerPhone $phone)
    {
        if (!$this->phones->contains($phone)) {
            $this->phones->add($phone);

            if ($phone->getOwner() !== $this) {
                $phone->setOwner($this);
            }
        }

        return $this;
    }

    /**
     * Remove phone
     *
     * @param B2bCustomerPhone $phone
     *
     * @return B2bCustomer
     */
    public function removePhone(B2bCustomerPhone $phone)
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    /**
     * Get phones
     *
     * @return Collection|B2bCustomerPhone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param B2bCustomerPhone $phone
     *
     * @return bool
     */
    public function hasPhone(B2bCustomerPhone $phone)
    {
        return $this->getPhones()->contains($phone);
    }

    /**
     * Gets primary phone if it's available.
     *
     * @return B2bCustomerPhone|null
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
     * @param B2bCustomerPhone $phone
     *
     * @return B2bCustomer
     */
    public function setPrimaryPhone(B2bCustomerPhone $phone)
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
     *
     * This method could not be named setEmails because of bug CRM-253.
     *
     * @param Collection|B2bCustomerEmail[] $emails
     *
     * @return B2bCustomer
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
     * @param B2bCustomerEmail $email
     *
     * @return B2bCustomer
     */
    public function addEmail(B2bCustomerEmail $email)
    {
        if (!$this->emails->contains($email)) {
            $this->emails->add($email);

            if ($email->getOwner() !== $this) {
                $email->setOwner($this);
            }
        }

        return $this;
    }

    /**
     * Remove email
     *
     * @param B2bCustomerEmail $email
     *
     * @return B2bCustomer
     */
    public function removeEmail(B2bCustomerEmail $email)
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
        }

        return $this;
    }

    /**
     * Get emails
     *
     * @return Collection|B2bCustomerEmail[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param B2bCustomerEmail $email
     *
     * @return bool
     */
    public function hasEmail(B2bCustomerEmail $email)
    {
        return $this->getEmails()->contains($email);
    }

    /**
     * Gets primary email if it's available.
     *
     * @return B2bCustomerEmail|null
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
     * @param B2bCustomerEmail $email
     *
     * @return B2bCustomer
     */
    public function setPrimaryEmail(B2bCustomerEmail $email)
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
}
