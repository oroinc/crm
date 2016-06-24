<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Model\ExtendLead;
use OroCRM\Bundle\ChannelBundle\Model\ChannelEntityTrait;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @ORM\Table(
 *      name="orocrm_sales_lead",
 *      indexes={@ORM\Index(name="lead_created_idx",columns={"createdAt"})}
 * )
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\SalesBundle\Entity\Repository\LeadRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="orocrm_sales_lead_index",
 *      routeView="orocrm_sales_lead_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-phone"
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
 *              "form_type"="orocrm_sales_lead_select",
 *              "grid_name"="sales-lead-grid",
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "grid"={
 *              "default"="sales-lead-grid",
 *              "context"="sales-lead-for-context-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Lead extends ExtendLead implements
    FullNameInterface,
    EmailHolderInterface,
    ChannelAwareInterface
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
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\LeadStatus")
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=10,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $status;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=150,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=20,
     *          "identity"=true
     *      }
     *  }
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=30
     *      }
     *  }
     * )
     */
    protected $namePrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=40
     *      }
     *  }
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=50
     *      }
     *  }
     * )
     */
    protected $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=60
     *      }
     *  }
     * )
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=70
     *      }
     *  }
     * )
     */
    protected $nameSuffix;

    /**
     * @var string
     *
     * @ORM\Column(name="job_title", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=80
     *      }
     *  }
     * )
     */
    protected $jobTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=90
     *      }
     *  }
     * )
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true},
     *          "importexport"={
     *              "order"=100
     *          },
     *          "entity"={
     *              "contact_information"="email"
     *          }
     *      }
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=110
     *      }
     *  }
     * )
     */
    protected $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=120
     *      }
     *  }
     * )
     */
    protected $website;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_employees", type="integer", nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=130
     *      }
     *  }
     * )
     */
    protected $numberOfEmployees;

    /**
     * @var string
     *
     * @ORM\Column(name="industry", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=140
     *      }
     *  }
     * )
     */
    protected $industry;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=170,
     *          "full"=true
     *      }
     *  }
     * )
     */
    protected $address;

    /**
     * @var \DateTime
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=180,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $owner;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity", mappedBy="lead")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=190,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $opportunities;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=140
     *      }
     *  }
     * )
     */
    protected $notes;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var B2bCustomer
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\B2bCustomer", inversedBy="leads")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "importexport"={
     *          "order"=160,
     *          "short"=true
     *      }
     *  }
     * )
     */
    protected $customer;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->opportunities = new ArrayCollection();
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
     * Set phone number
     *
     * @param string $phoneNumber
     *
     * @return Lead
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Lead
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * @return LeadStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param LeadStatus $status
     *
     * @return Lead
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return Lead
     */
    public function setAddress($address)
    {
        $this->address = $address;

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
     * Get contact last update date/time
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
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     * @ORM\PreUpdate
     */
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
     * @return Opportunity[]
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
     * @param WorkflowItem $workflowItem
     *
     * @return Lead
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowStep
     *
     * @return Lead
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * @param B2bCustomer $customer
     * @TODO remove null after BAP-5248
     */
    public function setCustomer(B2bCustomer $customer = null)
    {
        $this->customer = $customer;
    }

    /**
     * @return B2bCustomer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        if (!$this->status) {
            $em = $eventArgs->getEntityManager();
            /** @var LeadStatus $defaultStatus */
            $defaultStatus = $em->getReference('OroCRMSalesBundle:LeadStatus', 'new');
            $this->setStatus($defaultStatus);
        }
    }

    /**
     * Set organization
     *
     * @param Organization $organization
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
     * Remove Customer
     *
     * @return Lead
     */
    public function removeCustomer()
    {
        $this->customer = null;
    }
}
