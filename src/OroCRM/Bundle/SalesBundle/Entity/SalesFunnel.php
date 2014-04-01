<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * @ORM\Table(
 *      name="orocrm_sales_funnel",
 *      indexes={@ORM\Index(name="sales_start_idx",columns={"startDate"})}
 *
 * )
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\SalesBundle\Entity\Repository\SalesFunnelRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_sales_salesfunnel_index",
 *  routeView="orocrm_sales_salesfunnel_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-filter"
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
 *      "workflow"={
 *          "active_workflow"="b2b_flow_sales_funnel"
 *      }
 *  }
 * )
 */
class SalesFunnel
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    protected $startDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $owner;

    /**
     * @var Lead
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead", cascade={"persist"})
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $lead;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity", cascade={"persist"})
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $opportunity;

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
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $createdAt
     * @return SalesFunnel
     */
    public function setCreatedAt($createdAt)
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
     * @param \OroCRM\Bundle\SalesBundle\Entity\Lead $lead
     * @return SalesFunnel
     */
    public function setLead($lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return \OroCRM\Bundle\SalesBundle\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param \OroCRM\Bundle\SalesBundle\Entity\Opportunity $opportunity
     * @return SalesFunnel
     */
    public function setOpportunity($opportunity)
    {
        $this->opportunity = $opportunity;

        return $this;
    }

    /**
     * @return \OroCRM\Bundle\SalesBundle\Entity\Opportunity
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $owner
     * @return SalesFunnel
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \DateTime $startDate
     * @return SalesFunnel
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $updatedAt
     * @return SalesFunnel
     */
    public function setUpdatedAt($updatedAt)
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
     * @return SalesFunnel
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
     * @return SalesFunnel
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
}
