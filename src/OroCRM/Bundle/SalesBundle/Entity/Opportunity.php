<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Model\ExtendOpportunity;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")
 * @ORM\Table(
 *      name="orocrm_sales_opportunity",
 *      indexes={@ORM\Index(name="opportunity_created_idx",columns={"created_at"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_sales_opportunity_index",
 *  routeView="orocrm_sales_opportunity_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-usd"
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
 *          "form_type"="orocrm_sales_opportunity_select"
 *      },
 *      "dataaudit"={
 *          "auditable"=true
 *      }
 *  }
 * )
 */
class Opportunity extends ExtendOpportunity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var OpportunityStatus
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus")
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $status;

    /**
     * @var OpportunityCloseReason
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\OpportunityCloseReason")
     * @ORM\JoinColumn(name="close_reason_name", referencedColumnName="name")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $closeReason;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $contact;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $account;

    /**
     * @var Lead
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead", inversedBy="opportunities")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     **/
    protected $lead;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="close_date", type="date", nullable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $closeDate;

    /**
     * @var float
     *
     * @ORM\Column(name="probability", type="percent", nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "form"={
     *          "form_type"="oro_percent",
     *          "form_options"={
     *              "constraints"={{"Range":{"min":0, "max":100}}},
     *          }
     *      },
     *      "dataaudit"={
     *          "auditable"=true
     *      }
     *  }
     * )
     */
    protected $probability;

    /**
     * @var double
     *
     * @ORM\Column(name="budget_amount", type="money", nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "form"={
     *          "form_type"="oro_money",
     *          "form_options"={
     *              "constraints"={{"Range":{"min":0}}},
     *          }
     *      },
     *      "dataaudit"={
     *          "auditable"=true
     *      }
     *  }
     * )
     */
    protected $budgetAmount;

    /**
     * @var double
     *
     * @ORM\Column(name="close_revenue", type="money", nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "form"={
     *          "form_type"="oro_money",
     *          "form_options"={
     *              "constraints"={{"Range":{"min":0}}},
     *          }
     *      },
     *      "dataaudit"={
     *          "auditable"=true
     *      }
     *  }
     * )
     */
    protected $closeRevenue;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_need", type="text", nullable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $customerNeed;

    /**
     * @var string
     *
     * @ORM\Column(name="proposed_solution", type="text", nullable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
     */
    protected $proposedSolution;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Oro\Versioned
     * @ConfigField(defaultValues={"dataaudit"={"auditable"=true}})
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
     * @param WorkflowItem $workflowItem
     * @return Opportunity
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
     * @return Opportunity
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Account $account
     * @return Opportunity
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
     * @param Lead $lead
     * @return Opportunity
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
        return $this;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param float $budgetAmount
     * @return Opportunity
     */
    public function setBudgetAmount($budgetAmount)
    {
        $this->budgetAmount = $budgetAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getBudgetAmount()
    {
        return $this->budgetAmount;
    }

    /**
     * @param \DateTime $closeDate
     * @return Opportunity
     */
    public function setCloseDate($closeDate)
    {
        $this->closeDate = $closeDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCloseDate()
    {
        return $this->closeDate;
    }

    /**
     * @param Contact $contact
     * @return Opportunity
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
     * @param string $customerNeed
     * @return Opportunity
     */
    public function setCustomerNeed($customerNeed)
    {
        $this->customerNeed = $customerNeed;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerNeed()
    {
        return $this->customerNeed;
    }

    /**
     * @param float $probability
     * @return Opportunity
     */
    public function setProbability($probability)
    {
        $this->probability = $probability;
        return $this;
    }

    /**
     * @return float
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * @param string $proposedSolution
     * @return Opportunity
     */
    public function setProposedSolution($proposedSolution)
    {
        $this->proposedSolution = $proposedSolution;
        return $this;
    }

    /**
     * @return string
     */
    public function getProposedSolution()
    {
        return $this->proposedSolution;
    }

    /**
     * @param OpportunityStatus $status
     * @return Opportunity
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return OpportunityStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $name
     * @return Opportunity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param OpportunityCloseReason $closeReason
     * @return Opportunity
     */
    public function setCloseReason($closeReason)
    {
        $this->closeReason = $closeReason;
        return $this;
    }

    /**
     * @return OpportunityCloseReason
     */
    public function getCloseReason()
    {
        return $this->closeReason;
    }

    /**
     * @param float $revenue
     */
    public function setCloseRevenue($revenue)
    {
        $this->closeRevenue = $revenue;
    }

    /**
     * @return float
     */
    public function getCloseRevenue()
    {
        return $this->closeRevenue;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $created
     * @return Opportunity
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;
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
     * @param \DateTime $updated
     * @return Opportunity
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = $updated;
        return $this;
    }

    public function __toString()
    {
        return (string)$this->getName();
    }
    /**
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->beforeUpdate();
    }

    /**
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
     * @return Opportunity
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

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
     * @return Opportunity
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        /** @var LeadStatus $defaultStatus */
        $defaultStatus   = $em->getReference('OroCRMSalesBundle:OpportunityStatus', 'in_progress');
        $this->setStatus($defaultStatus);
    }
}
