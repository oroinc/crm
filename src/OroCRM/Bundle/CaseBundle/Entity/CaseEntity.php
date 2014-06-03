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
     * @ORM\JoinColumn(name="origin_code", referencedColumnName="code", onDelete="SET NULL")
     */
    protected $origin;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter_user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporterUser;

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
     * @param \DateTime $closedAt
     *
     * @return $this
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
     * @param \DateTime $reportedAt
     *
     * @return $this
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
     * @param \DateTime $createdAt
     *
     * @return $this
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
     * @param string $description
     *
     * @return $this
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
     * @param integer $id
     *
     * @return $this
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
     * @param User $owner
     *
     * @return $this
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
     * @param string $subject
     *
     * @return $this
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
     * @param \DateTime $updatedAt
     *
     * @return $this
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
     * @param WorkflowItem $workflowItem
     *
     * @return $this
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
     * @param WorkflowStep $workflowStep
     *
     * @return $this
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
     * @param CaseOrigin $origin
     *
     * @return $this
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
     * @param Cart $relatedCart
     *
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @param Order $relatedOrder
     *
     * @return $this
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
     * @param Contact $reporterContact
     *
     * @return $this
     */
    public function setReporterContact(Contact $reporterContact)
    {
        $this->reporterContact = $reporterContact;

        return $this;
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
     *
     * @return $this
     */
    public function setReporterCustomer(Customer $reporterCustomer)
    {
        $this->reporterCustomer = $reporterCustomer;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getReporterCustomer()
    {
        return $this->reporterCustomer;
    }

    /**
     * @param User $reporterUser
     *
     * @return $this
     */
    public function setReporterUser(User $reporterUser)
    {
        $this->reporterUser = $reporterUser;

        return $this;
    }

    /**
     * @return User
     */
    public function getReporterUser()
    {
        return $this->reporterUser;
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
