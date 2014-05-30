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
     * @ORM\JoinColumn(name="reporter_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $reporter;

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
    protected $reportedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closedOn;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=100, nullable=true)
     */
    protected $emailAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=100, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=100, nullable=true)
     */
    protected $web;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=100, nullable=true)
     */
    protected $otherContact;

    /**
     * @param \DateTime $closedOn
     */
    public function setClosedOn(\DateTime $closedOn)
    {
        $this->closedOn = $closedOn;
    }

    /**
     * @return \DateTime
     */
    public function getClosedOn()
    {
        return $this->closedOn;
    }

    /**
     * @param Cart $relatedCart
     */
    public function setRelatedCart(Cart $relatedCart)
    {
        $this->relatedCart = $relatedCart;
    }

    /**
     * @return Cart
     */
    public function getRelatedCart()
    {
        return $this->relatedCart;
    }

    /**
     * @param \DateTime $reportedOn
     */
    public function setReportedOn(\DateTime $reportedOn)
    {
        $this->reportedOn = $reportedOn;
    }

    /**
     * @return \DateTime
     */
    public function getReportedOn()
    {
        return $this->reportedOn;
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
    public function setOwner(User $owner)
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
    public function setUpdatedAt(\DateTime $updatedAt)
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
     * @return string
     */
    public function getWorkflowStepName()
    {
        return $this->getWorkflowStep() ? $this->getWorkflowStep()->getName() : null;
    }

    /**
     * @param Lead $relatedLead
     */
    public function setRelatedLead(Lead $relatedLead)
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
    public function setRelatedOpportunity(Opportunity $relatedOpportunity)
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
    public function setRelatedOrder(Order $relatedOrder)
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
     * @param Contact $reporterContact
     */
    public function setReporterContact(Contact $reporterContact)
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
    public function setReporterCustomer(Customer $reporterCustomer)
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

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $otherContact
     */
    public function setOtherContact($otherContact)
    {
        $this->otherContact = $otherContact;
    }

    /**
     * @return string
     */
    public function getOtherContact()
    {
        return $this->otherContact;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param User $reporter
     */
    public function setReporter(User $reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @param string $web
     */
    public function setWeb($web)
    {
        $this->web = $web;
    }

    /**
     * @return string
     */
    public function getWeb()
    {
        return $this->web;
    }
}
