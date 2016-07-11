<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Model\ExtendB2bCustomer;
use OroCRM\Bundle\ChannelBundle\Model\ChannelEntityTrait;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository")
 * @ORM\Table(name="orocrm_sales_b2bcustomer")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="orocrm_sales_b2bcustomer_index",
 *      routeView="orocrm_sales_b2bcustomer_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-user-md"
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
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "form"={
 *              "form_type"="orocrm_sales_b2bcustomer_select"
 *          },
 *          "grid"={
 *              "default"="orocrm-sales-b2bcustomers-grid",
 *              "context"="orocrm-sales-b2bcustomers-for-context-grid"
 *          },
 *         "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class B2bCustomer extends ExtendB2bCustomer implements
    EmailOwnerInterface,
    ChannelAwareInterface,
    CustomerIdentityInterface
{
    use ChannelEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=0
     *      }
     *  }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=10
     *          }
     *      }
     * )
     */
    protected $name;

    /**
     * @var double
     *
     * @ORM\Column(name="lifetime", type="money", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "full"=true,
     *              "order"=15
     *          }
     *      }
     * )
     */
    protected $lifetime = 0;

    /**
     * @var Address $shippingAddress
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true,
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $shippingAddress;

    /**
     * @var Address $billingAddress
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true,
     *              "order"=30
     *          }
     *      }
     * )
     */
    protected $billingAddress;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", cascade="persist")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=40,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $account;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=50,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $contact;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead", mappedBy="customer", cascade={"remove"})
     */
    protected $leads;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity",
     *     mappedBy="customer",
     *     cascade={"remove"}
     * )
     */
    protected $opportunities;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=70,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
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
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone", mappedBy="owner",
     *    mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * ))
     * @ORM\OrderBy({"primary" = "DESC"})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=80
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $phones;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail",
     *    mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=90
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $emails;

    public function __construct()
    {
        parent::__construct();

        $this->leads         = new ArrayCollection();
        $this->opportunities = new ArrayCollection();
        $this->phones        = new ArrayCollection();
        $this->emails        = new ArrayCollection();
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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
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
     */
    public function setShippingAddress(Address $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;
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
     */
    public function setBillingAddress(Address $billingAddress = null)
    {
        $this->billingAddress = $billingAddress;
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
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;
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
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @param ArrayCollection $leads
     */
    public function setLeads(ArrayCollection $leads)
    {
        $this->leads = $leads;
    }

    /**
     * @param Lead $lead
     */
    public function addLead(Lead $lead)
    {
        if (!$this->getLeads()->contains($lead)) {
            $this->getLeads()->add($lead);
            $lead->setCustomer($this);
        }
    }

    /**
     * @param Lead $lead
     */
    public function removeLead(Lead $lead)
    {
        if ($this->getLeads()->contains($lead)) {
            $this->getLeads()->removeElement($lead);
            $lead->removeCustomer();
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getOpportunities()
    {
        return $this->opportunities;
    }

    /**
     * @param ArrayCollection $opportunities
     */
    public function setOpportunities(ArrayCollection $opportunities)
    {
        $this->opportunities = $opportunities;
    }

    /**
     * @param Opportunity $opportunity
     */
    public function addOpportunity(Opportunity $opportunity)
    {
        if (!$this->getOpportunities()->contains($opportunity)) {
            $this->getOpportunities()->add($opportunity);
            $opportunity->setCustomer($this);
        }
    }

    /**
     * @param Opportunity $opportunity
     */
    public function removeOpportunity(Opportunity $opportunity)
    {
        if ($this->getOpportunities()->contains($opportunity)) {
            $this->getOpportunities()->removeElement($opportunity);
            $opportunity->removeCustomer();
        }
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
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
     * @param Organization $organization
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
            $phone->setOwner($this);
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
            $email->setOwner($this);
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
    
    /**
     * Get entity class name.
     * TODO: Remove this temporary solution for get 'view' route in twig after EntityConfigBundle is finished
     * @return string
     */
    public function getClass()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer';
    }

    /**
     * Get names of fields contain email addresses
     *
     * @return string[]|null
     */
    public function getEmailFields()
    {
        return null;
    }

    /** Stub for EmailOwnerInterface */
    public function getFirstName()
    {
        return null;
    }

    /** Stub for EmailOwnerInterface */
    public function getLastName()
    {
        return null;
    }
}
