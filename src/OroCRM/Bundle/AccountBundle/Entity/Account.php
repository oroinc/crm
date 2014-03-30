<?php

namespace OroCRM\Bundle\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use OroCRM\Bundle\AccountBundle\Model\ExtendAccount;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_account", indexes={@ORM\Index(name="account_name_idx", columns={"name"})})
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_account_index",
 *  routeView="orocrm_account_view",
 *  defaultValues={
 *      "entity"={
 *          "label"="Account",
 *          "plural_label"="Accounts",
 *          "icon"="icon-suitcase"
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
 *      "merge"={
 *          "enable"=true
 *      },
 *      "form"={
 *          "form_type"="orocrm_account_select"
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class Account extends ExtendAccount implements Taggable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Soap\ComplexType("string")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *    "merge"={
     *      "display"=true
     *    },
     *    "dataaudit"={
     *      "auditable"=true
     *    }
     *   }
     * )
     */
    protected $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *    "merge"={
     *      "display"=true
     *    },
     *    "dataaudit"={
     *      "auditable"=true
     *    }
     *  }
     * )
     */
    protected $owner;

    /**
     * @var Address $shippingAddress
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(defaultValues={"merge"={"display"=true}})
     */
    protected $shippingAddress;

    /**
     * @var Address $billingAddress
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(defaultValues={"merge"={"display"=true}})
     */
    protected $billingAddress;

    /**
     * Contacts storage
     *
     * @var ArrayCollection $contacts
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", inversedBy="accounts")
     * @ORM\JoinTable(name="orocrm_account_to_contact")
     * @ConfigField(defaultValues={"merge"={"display"=true}})
     */
    protected $contacts;

    /**
     * Default contact entity
     *
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="default_contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(defaultValues={"merge"={"display"=true}})
     */
    protected $defaultContact;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    /**
     * @var ArrayCollection $tags
     * @ConfigField(defaultValues={"merge"={"display"=true}})
     */
    protected $tags;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    /**
     * Returns the account unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set account name
     *
     * @param  string  $name New name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime
     * @return Account
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime
     * @return Account
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    /**
     * Get contacts collection
     *
     * @return Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add specified contact
     *
     * @param Contact $contact
     * @return Account
     */
    public function addContact(Contact $contact)
    {
        if (!$this->getContacts()->contains($contact)) {
            $this->getContacts()->add($contact);
            $contact->addAccount($this);
        }

        return $this;
    }

    /**
     * Set contacts collection
     *
     * @param Collection $contacts
     * @return Account
     */
    public function setContacts(Collection $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get shipping address
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Set shipping address
     *
     * @param Address $address
     * @return Account
     */
    public function setShippingAddress($address)
    {
        $this->shippingAddress = $address;

        return $this;
    }

    /**
     * Get shipping address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set billing address
     *
     * @param Address $address
     * @return Account
     */
    public function setBillingAddress($address)
    {
        $this->billingAddress = $address;

        return $this;
    }

    /**
     * Remove specified contact
     *
     * @param Contact $contact
     * @return Account
     */
    public function removeContact(Contact $contact)
    {
        if ($this->getContacts()->contains($contact)) {
            $this->getContacts()->removeElement($contact);
            $contact->removeAccount($this);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

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
     * @param User $owningUser
     * @return Account
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @param Contact $defaultContact
     * @return Account
     */
    public function setDefaultContact($defaultContact)
    {
        $this->defaultContact = $defaultContact;

        if ($defaultContact && !$this->contacts->contains($defaultContact)) {
            $this->addContact($defaultContact);
        }

        return $this;
    }

    /**
     * @return Contact
     */
    public function getDefaultContact()
    {
        return $this->defaultContact;
    }
}
