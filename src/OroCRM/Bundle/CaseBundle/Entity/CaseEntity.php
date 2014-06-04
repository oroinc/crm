<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_case"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  routeName="orocrm_case_index",
 *  routeView="orocrm_case_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-list-alt"
 *      },
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL"
 *      },
 *      "workflow"={
 *          "active_workflow"="case_flow"
 *      },
 *  }
 * )
 */
class CaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var CaseOrigin
     *
     * @ORM\ManyToOne(targetEntity="CaseOrigin", cascade={"persist"})
     * @ORM\JoinColumn(name="origin_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $origin;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Order")
     * @ORM\JoinColumn(name="related_order_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedOrder;

    /**
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Cart")
     * @ORM\JoinColumn(name="related_cart_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedCart;

    /**
     * @var Lead
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead")
     * @ORM\JoinColumn(name="related_lead_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedLead;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="related_opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedOpportunity;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="related_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedContact;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Customer")
     * @ORM\JoinColumn(name="related_customer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedCustomer;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporter;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $reportedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closedAt;

    /**
     * @param integer $id
     *
     * @return CaseEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $subject
     *
     * @return CaseEntity
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $description
     *
     * @return CaseEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param User $owner
     *
     * @return CaseEntity
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

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
     * @param CaseOrigin $origin
     *
     * @return CaseEntity
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return CaseOrigin
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param Order $relatedOrder
     *
     * @return CaseEntity
     */
    public function setRelatedOrder(Order $relatedOrder)
    {
        $this->relatedOrder = $relatedOrder;

        return $this;
    }

    /**
     * @return Order
     */
    public function getRelatedOrder()
    {
        return $this->relatedOrder;
    }

    /**
     * @param Cart $relatedCart
     *
     * @return CaseEntity
     */
    public function setRelatedCart(Cart $relatedCart)
    {
        $this->relatedCart = $relatedCart;

        return $this;
    }

    /**
     * @return Cart
     */
    public function getRelatedCart()
    {
        return $this->relatedCart;
    }

    /**
     * @param Lead $relatedLead
     *
     * @return CaseEntity
     */
    public function setRelatedLead(Lead $relatedLead)
    {
        $this->relatedLead = $relatedLead;

        return $this;
    }

    /**
     * @return Lead
     */
    public function getRelatedLead()
    {
        return $this->relatedLead;
    }

    /**
     * @param Opportunity $relatedOpportunity
     *
     * @return CaseEntity
     */
    public function setRelatedOpportunity(Opportunity $relatedOpportunity)
    {
        $this->relatedOpportunity = $relatedOpportunity;

        return $this;
    }

    /**
     * @return Opportunity
     */
    public function getRelatedOpportunity()
    {
        return $this->relatedOpportunity;
    }

    /**
     * @param Contact $relatedContact
     *
     * @return CaseEntity
     */
    public function setRelatedContact(Contact $relatedContact)
    {
        $this->relatedContact = $relatedContact;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getRelatedContact()
    {
        return $this->relatedContact;
    }

    /**
     * @param Customer $relatedCustomer
     *
     * @return CaseEntity
     */
    public function setRelatedCustomer(Customer $relatedCustomer)
    {
        $this->relatedCustomer = $relatedCustomer;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getRelatedCustomer()
    {
        return $this->relatedCustomer;
    }

    /**
     * @param User $reporter
     *
     * @return CaseEntity
     */
    public function setReporter(User $reporter)
    {
        $this->reporter = $reporter;

        return $this;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @param WorkflowStep $workflowStep
     *
     * @return CaseEntity
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
     * @return string
     */
    public function getWorkflowStepName()
    {
        return $this->getWorkflowStep() ? $this->getWorkflowStep()->getName() : null;
    }

    /**
     * @param WorkflowItem $workflowItem
     *
     * @return CaseEntity
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
     * @param \DateTime $createdAt
     *
     * @return CaseEntity
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

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
     * @param \DateTime $updatedAt
     *
     * @return CaseEntity
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
     * @param \DateTime $reportedAt
     *
     * @return CaseEntity
     */
    public function setReportedAt(\DateTime $reportedAt)
    {
        $this->reportedAt = $reportedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReportedAt()
    {
        return $this->reportedAt;
    }

    /**
     * @param \DateTime $closedAt
     *
     * @return CaseEntity
     */
    public function setClosedAt(\DateTime $closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt  = $this->createdAt ? $this->createdAt : new \DateTime();
        $this->reportedAt = $this->reportedAt? $this->reportedAt : new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
