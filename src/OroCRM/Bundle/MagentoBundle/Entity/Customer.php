<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePerson;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * Class Customer
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_magento_customer",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="unq_origin_id_channel_id", columns={"origin_id", "channel_id"})},
 *      indexes={@ORM\Index(name="magecustomer_name_idx",columns={"first_name", "last_name"})}
 * )
 * @Config(
 *  routeName="orocrm_magento_customer_index",
 *  routeView="orocrm_magento_customer_view",
 *  defaultValues={
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 * @Oro\Loggable
 */
class Customer extends BasePerson
{
    use IntegrationEntityTrait, OriginTrait;

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
     */
    protected $email;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     * @Oro\Versioned
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @Oro\Versioned
     */
    protected $updatedAt;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Website", cascade="PERSIST")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $website;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Store", cascade="PERSIST")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $store;

    /**
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup", cascade="PERSIST")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $group;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", cascade="PERSIST")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $contact;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", cascade="PERSIST")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Address",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     */
    protected $addresses;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Cart",
     *     mappedBy="customer", cascade={"all"}, orphanRemoval=true
     * )
     */
    protected $carts;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Order",
     *     mappedBy="customer", cascade={"all"}, orphanRemoval=true
     * )
     */
    protected $orders;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_active")
     */
    protected $isActive = false;

    /**
     * @var string
     *
     * @ORM\Column(name="vat", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $vat;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->orders = new ArrayCollection();
    }

    /**
     * @param Website $website
     *
     * @return $this
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
     * @param Store $store
     *
     * @return $this
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
     * @param CustomerGroup $group
     *
     * @return $this
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
     * @return $this
     */
    public function setContact(Contact $contact)
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
     * @return $this
     */
    public function setAccount(Account $account)
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
     * @return $this
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
     * @return $this
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
            function ($item) use ($originId) {
                return $item->getOriginId() == $originId;
            }
        )->first();
    }
}
