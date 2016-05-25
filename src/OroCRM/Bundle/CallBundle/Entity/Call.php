<?php

namespace OroCRM\Bundle\CallBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;

use OroCRM\Bundle\CallBundle\Model\ExtendCall;

/**
 * Call
 *
 * @ORM\Table(
 *      name="orocrm_call",
 *      indexes={@ORM\Index(name="call_dt_idx",columns={"call_date_time"})}
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="orocrm_call_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-phone-sign"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="orocrm_call_activity_view",
 *              "acl"="orocrm_call_view",
 *              "action_button_widget"="orocrm_log_call_button",
 *              "action_link_widget"="orocrm_log_call_link"
 *          },
 *          "grid"={
 *              "default"="calls-grid",
 *              "context"="call-for-context-grid"
 *          }
 *      }
 * )
 */
class Call extends ExtendCall implements DatesAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Soap\ComplexType("string")
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="call_date_time", type="datetime")
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $callDateTime;

    /**
     * @var CallStatus
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\CallBundle\Entity\CallStatus")
     * @ORM\JoinColumn(name="call_status_name", referencedColumnName="name", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $callStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="duration", nullable=true)
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $duration;

    /**
     * @var CallDirection
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\CallBundle\Entity\CallDirection")
     * @ORM\JoinColumn(name="call_direction_name", referencedColumnName="name", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $direction;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $organization;

    /**
     * @var bool
     */
    protected $updatedAtSet;

    public function __construct()
    {
        parent::__construct();
        $this->callDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->duration = 0;
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
     * Set subject
     *
     * @param string $subject
     * @return Call
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Call
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Call
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set callDateTime
     *
     * @param \DateTime $callDateTime
     * @return Call
     */
    public function setCallDateTime($callDateTime)
    {
        $this->callDateTime = $callDateTime;

        return $this;
    }

    /**
     * Get callDateTime
     *
     * @return \DateTime
     */
    public function getCallDateTime()
    {
        return $this->callDateTime;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return Call
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set direction
     *
     * @param CallDirection $direction
     * @return Call
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get direction
     *
     * @return CallDirection
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Set owner
     *
     * @param User $owner
     * @return Call
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set callStatus
     *
     * @param CallStatus $callStatus
     * @return Call
     */
    public function setCallStatus($callStatus)
    {
        $this->callStatus = $callStatus;

        return $this;
    }

    /**
     * Get callStatus
     *
     * @return CallStatus
     */
    public function getCallStatus()
    {
        return $this->callStatus;
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
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

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
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAtSet = false;
        if ($updatedAt !== null) {
            $this->updatedAtSet = true;
        }

        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdatedAtSet()
    {
        return $this->updatedAtSet;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Call
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getSubject();
    }
}
