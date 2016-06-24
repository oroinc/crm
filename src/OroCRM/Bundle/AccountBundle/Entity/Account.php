<?php

namespace OroCRM\Bundle\AccountBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Model\NameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AccountBundle\Model\ExtendAccount;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_account", indexes={@ORM\Index(name="account_name_idx", columns={"name"})})
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="orocrm_account_index",
 *      routeView="orocrm_account_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-suitcase"
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
 *              "category"="account_management"
 *          },
 *          "merge"={
 *              "enable"=true
 *          },
 *          "form"={
 *              "form_type"="orocrm_account_select",
 *              "grid_name"="accounts-select-grid",
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "grid"={
 *              "default"="accounts-grid",
 *              "context"="accounts-for-context-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class Account extends ExtendAccount implements EmailHolderInterface, NameInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=10
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Soap\ComplexType("string")
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=20
     *          }
     *      }
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
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "order"=30,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * Contacts storage
     *
     * @var ArrayCollection $contacts
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", inversedBy="accounts")
     * @ORM\JoinTable(name="orocrm_account_to_contact")
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "importexport"={
     *              "order"=50,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $contacts;

    /**
     * Default contact entity
     *
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", inversedBy="defaultInAccounts")
     * @ORM\JoinColumn(name="default_contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "importexport"={
     *              "order"=40,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $defaultContact;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var Account
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="referred_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $referredBy;

    public function __construct()
    {
        parent::__construct();

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
     * @param  int     $id
     * @return Account
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
     * @param string $name New name
     *
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
     *
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
     *
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
     * @return Collection|Contact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add specified contact
     *
     * @param Contact $contact
     *
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
     *
     * @return Account
     */
    public function setContacts(Collection $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Remove specified contact
     *
     * @param Contact $contact
     *
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
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     *
     * @return Account
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @param Contact $defaultContact
     *
     * @return Account
     */
    public function setDefaultContact($defaultContact)
    {
        if ($this->defaultContact === $defaultContact) {
            return $this;
        }

        /**
         * As resolving of $this->defaultContact->getDefaultInAccounts() lazy collection will
         * overwrite $this->defaultContact to value from db, make sure the collection is resolved
         */
        if ($this->defaultContact) {
            $this->defaultContact->getDefaultInAccounts()->toArray();
        }

        $originalContact = $this->defaultContact;
        $this->defaultContact = $defaultContact;

        if ($defaultContact) {
            $defaultContact->addDefaultInAccount($this);
        }

        if ($originalContact) {
            $originalContact->removeDefaultInAccount($this);
        }

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

    /**
     * Get the primary email address of the default contact
     *
     * @return string
     */
    public function getEmail()
    {
        $contact = $this->getDefaultContact();
        if (!$contact) {
            return null;
        }

        return $contact->getEmail();
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return Account
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
     * @return Account
     */
    public function getReferredBy()
    {
        return $this->referredBy;
    }

    /**
     * @param Account $referredBy
     *
     * @return Account
     */
    public function setReferredBy(Account $referredBy = null)
    {
        $this->referredBy = $referredBy;

        return $this;
    }
}
