<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareTrait;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Model\ExtendCustomer;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;

/**
 * Class Customer
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository")
 * @ORM\Table(
 *      name="orocrm_magento_customer",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="magecustomer_oid_cid_unq", columns={"origin_id", "channel_id"})},
 *      indexes={
 *          @ORM\Index(name="magecustomer_name_idx",columns={"first_name", "last_name"}),
 *          @ORM\Index(name="magecustomer_rev_name_idx",columns={"last_name", "first_name"}),
 *          @ORM\Index(name="magecustomer_email_guest_idx",columns={"email"})
 *      }
 * )
 * @Config(
 *      routeName="orocrm_magento_customer_index",
 *      routeView="orocrm_magento_customer_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-user"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
 *          },
 *          "form"={
 *              "grid_name"="magento-customers-grid",
 *          },
 *          "grid"={
 *              "default"="magento-customers-grid",
 *              "context"="magento-customers-for-context-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 * @Oro\Loggable
 */
class Customer extends ExtendCustomer implements
    ChannelAwareInterface,
    CustomerIdentityInterface,
    RFMAwareInterface,
    OriginAwareInterface,
    IntegrationAwareInterface
{
    const SYNC_TO_MAGENTO = 1;
    const MAGENTO_REMOVED = 2;

    use IntegrationEntityTrait, OriginTrait, ChannelEntityTrait, RFMAwareTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /*
     * FIELDS are duplicated to enable dataaudit only for customer fields
     */
    /**
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $namePrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $nameSuffix;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=8, nullable=true)
     * @Oro\Versioned
     */
    protected $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Oro\Versioned
     */
    protected $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "contact_information"="email"
     *          }
     *      }
     * )
     */
    protected $email;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="imported_at", nullable=true)
     * @Oro\Versioned
     */
    protected $importedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="synced_at", nullable=true)
     * @Oro\Versioned
     */
    protected $syncedAt;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Website")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=false
     *          }
     *      }
     * )
     */
    protected $website;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Store")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=false
     *          }
     *      }
     * )
     */
    protected $store;

    /**
     * @var string
     *
     * @ORM\Column(name="created_in", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $createdIn;

    /**
     * @var bool
     * @ORM\Column(name="is_confirmed", type="boolean", nullable=true)
     */
    protected $confirmed = true;

    /**
     * @var bool
     * @ORM\Column(name="is_guest", type="boolean", nullable=false, options={"default"=false})
     */
    protected $guest = false;

    /**
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=false
     *          }
     *      }
     * )
     */
    protected $group;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $contact;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $account;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Address",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          }
     *      }
     * )
     */
    protected $addresses;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Cart",
     *     mappedBy="customer", cascade={"remove"}, orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $carts;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Order",
     *     mappedBy="customer", cascade={"remove"}, orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $orders;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_active")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $isActive = false;

    /**
     * @var float
     *
     * @ORM\Column(name="vat", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $vat;

    /**
     * @var float
     *
     * @ORM\Column(name="lifetime", type="money", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $lifetime = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=10, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $currency;

    /**
     * @var int
     *
     * @ORM\Column(name="sync_state", type="integer", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $syncState;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=32, nullable=true)
     */
    protected $password;

    /**
     * @var NewsletterSubscriber[]|Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber",
     *      mappedBy="customer", cascade={"remove"}, orphanRemoval=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $newsletterSubscribers;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->carts  = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->newsletterSubscribers = new ArrayCollection();
    }

    /**
     * @param Website $website
     *
     * @return Customer
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return string
     */
    public function getWebsiteName()
    {
        return $this->website ? $this->website->getName() : null;
    }

    /**
     * @param Store $store
     *
     * @return Customer
     */
    public function setStore(Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return string|null
     */
    public function getStoreName()
    {
        return $this->store ? $this->store->getName() : null;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     * @return Customer
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->guest;
    }

    /**
     * @param bool $guest
     * @return Customer
     */
    public function setGuest($guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedIn()
    {
        return $this->createdIn;
    }

    /**
     * @param string $createdIn
     * @return Customer
     */
    public function setCreatedIn($createdIn)
    {
        $this->createdIn = $createdIn;

        return $this;
    }

    /**
     * @param CustomerGroup $group
     *
     * @return Customer
     */
    public function setGroup(CustomerGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return CustomerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Contact $contact
     *
     * @return Customer
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
     * @param Account $account
     *
     * @return Customer
     */
    public function setAccount($account)
    {
        $this->account = $account;

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
     * @param string $vat
     *
     * @return Customer
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param bool $isActive
     *
     * @return Customer
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param Collection $carts
     */
    public function setCarts($carts)
    {
        $this->carts = $carts;
    }

    /**
     * @return Collection
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * @return Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param int $originId
     *
     * @return Address|false
     */
    public function getAddressByOriginId($originId)
    {
        return $this->addresses->filter(
            function (Address $item) use ($originId) {
                return $item->getOriginId() === $originId;
            }
        )->first();
    }

    /**
     * @param double $lifetime
     *
     * @return Customer
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return double
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param string $currency
     *
     * @return Customer
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Add address
     *
     * @param AbstractAddress $address
     * @return Customer
     */
    public function addAddress(AbstractAddress $address)
    {
        /** @var Address $address */
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setOwner($this);
        }

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
     * @param User $user
     */
    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Customer
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
     * {@inheritdoc}
     */
    public function getSyncState()
    {
        return $this->syncState;
    }

    /**
     * {@inheritdoc}
     *
     * @return Customer
     */
    public function setSyncState($syncState)
    {
        $this->syncState = $syncState;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Customer
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $password
     * @return Customer
     */
    public function setGeneratedPassword($password)
    {
        if ($password) {
            $this->setPassword($password);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGeneratedPassword()
    {
        return '';
    }

    /**
     * @return NewsletterSubscriber[]|Collection
     */
    public function getNewsletterSubscribers()
    {
        return $this->newsletterSubscribers;
    }

    /**
     * @param Collection|NewsletterSubscriber[] $newsletterSubscribers
     * @return Customer
     */
    public function setNewsletterSubscribers($newsletterSubscribers)
    {
        $this->newsletterSubscribers = $newsletterSubscribers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubscribed()
    {
        foreach ($this->getNewsletterSubscribers() as $newsletterSubscriber) {
            if ($newsletterSubscriber->isSubscribed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \DateTime
     */
    public function getSyncedAt()
    {
        return $this->syncedAt;
    }

    /**
     * @param \DateTime $syncedAt
     * @return Customer
     */
    public function setSyncedAt(\DateTime $syncedAt)
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * @param \DateTime $importedAt
     * @return Customer
     */
    public function setImportedAt(\DateTime $importedAt)
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getLastName() . (string)$this->getFirstName();
    }
}
