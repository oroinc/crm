<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Model\ExtendOpportunity;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_sales_opportunity")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Opportunity", "plural_label"="Opportunities"},
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
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
     **/
    protected $status;

    /**
     * @var OpportunityCloseReason
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\OpportunityCloseReason")
     * @ORM\JoinColumn(name="close_reason_name", referencedColumnName="name")
     * @Oro\Versioned
     **/
    protected $closeReason;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     **/
    protected $contact;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     **/
    protected $account;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="close_date", type="date", nullable=true)
     * @Oro\Versioned
     */
    protected $closeDate;

    /**
     * @var float
     *
     * @ORM\Column(name="probability", type="float", nullable=true)
     * @Oro\Versioned
     */
    protected $probability;

    /**
     * @var float
     *
     * @ORM\Column(name="budget_amount", type="float", nullable=true)
     * @Oro\Versioned
     */
    protected $budgetAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="close_revenue", type="float", nullable=true)
     * @Oro\Versioned
     */
    protected $closeRevenue;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_need", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $customerNeed;

    /**
     * @var string
     *
     * @ORM\Column(name="proposed_solution", type="string", length=255, nullable=true)
     * @Oro\Versioned
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
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->preUpdate();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
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
}
