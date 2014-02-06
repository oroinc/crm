<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(name="orocrm_sales_funnel")
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\SalesBundle\Entity\Repository\SalesFunnelRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_sales_funnel_index",
 *  routeView="orocrm_sales_funnel_view",
 *  defaultValues={
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Oro\Versioned
     */
    protected $name;

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
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $lead;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity")
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
     * @return SalesFunnel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
