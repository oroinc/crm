<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

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
 *      name="oro_ticket"
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignedTo;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="reporter_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporterContact;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Customer")
     * @ORM\JoinColumn(name="reporter_customer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporterCustomer;

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
     * @ORM\JoinColumn(name="related_shopping_cart_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedShoppingCart;

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
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

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
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @param User $assignedTo
     */
    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;
    }

    /**
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param WorkflowItem $workflowItem
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowStep $workflowStep
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * @param Lead $relatedLead
     */
    public function setRelatedLead($relatedLead)
    {
        $this->relatedLead = $relatedLead;
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
     */
    public function setRelatedOpportunity($relatedOpportunity)
    {
        $this->relatedOpportunity = $relatedOpportunity;
    }

    /**
     * @return Opportunity
     */
    public function getRelatedOpportunity()
    {
        return $this->relatedOpportunity;
    }

    /**
     * @param Order $relatedOrder
     */
    public function setRelatedOrder($relatedOrder)
    {
        $this->relatedOrder = $relatedOrder;
    }

    /**
     * @return Order
     */
    public function getRelatedOrder()
    {
        return $this->relatedOrder;
    }

    /**
     * @param Cart $relatedShoppingCart
     */
    public function setRelatedShoppingCart($relatedShoppingCart)
    {
        $this->relatedShoppingCart = $relatedShoppingCart;
    }

    /**
     * @return Cart
     */
    public function getRelatedShoppingCart()
    {
        return $this->relatedShoppingCart;
    }

    /**
     * @param Contact $reporterContact
     */
    public function setReporterContact($reporterContact)
    {
        $this->reporterContact = $reporterContact;
    }

    /**
     * @return Contact
     */
    public function getReporterContact()
    {
        return $this->reporterContact;
    }

    /**
     * @param Customer $reporterCustomer
     */
    public function setReporterCustomer($reporterCustomer)
    {
        $this->reporterCustomer = $reporterCustomer;
    }

    /**
     * @return Customer
     */
    public function getReporterCustomer()
    {
        return $this->reporterCustomer;
    }
}
