<?php

namespace OroCRM\Bundle\ContactUsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_contactus_request",
 *      indexes={@ORM\Index(name="request_create_idx",columns={"created_at"})}
 * )
 *
 * @Config(
 *  routeName="orocrm_contactus_request_index",
 *  defaultValues={
 *      "entity"={"icon"="icon-envelope"},
 *      "security"={
 *          "type"="ACL",
 *          "permissions"="All",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class ContactRequest extends AbstractContactRequest
{
    const CONTACT_METHOD_BOTH  = 'orocrm.contactus.contactrequest.method.both';
    const CONTACT_METHOD_PHONE = 'orocrm.contactus.contactrequest.method.phone';
    const CONTACT_METHOD_EMAIL = 'orocrm.contactus.contactrequest.method.email';

    /**
     * @var string
     *
     * @ORM\Column(name="organization_name", type="string", nullable=true)
     */
    protected $organizationName;

    /**
     * @var string
     *
     * @ORM\Column(name="preferred_contact_method", type="string", length=100)
     */
    protected $preferredContactMethod = self::CONTACT_METHOD_EMAIL;

    /**
     * @var ContactReason
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactUsBundle\Entity\ContactReason")
     * @ORM\JoinColumn(name="contact_reason_id", referencedColumnName="id", nullable=true)
     **/
    protected $contactReason;

    /**
     * @var string
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    protected $feedback;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $opportunity;

    /**
     * @var Lead
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Lead")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $lead;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\CallBundle\Entity\Call")
     * @ORM\JoinTable(name="orocrm_contactus_request_calls",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="call_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $calls;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\EmailBundle\Entity\Email")
     * @ORM\JoinTable(name="orocrm_contactus_req_emails",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="email_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $emails;

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

    public function __construct()
    {
        $this->calls  = new ArrayCollection();
        $this->emails = new ArrayCollection();
    }

    /**
     * @param string $organizationName
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param string $preferredContactMethod
     */
    public function setPreferredContactMethod($preferredContactMethod)
    {
        $this->preferredContactMethod = $preferredContactMethod;
    }

    /**
     * @return string
     */
    public function getPreferredContactMethod()
    {
        return $this->preferredContactMethod;
    }

    /**
     * @param ContactReason $contactReason
     */
    public function setContactReason(ContactReason $contactReason = null)
    {
        $this->contactReason = $contactReason;
    }

    /**
     * @return ContactReason
     */
    public function getContactReason()
    {
        return $this->contactReason;
    }

    /**
     * @param string $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param Lead $lead
     */
    public function setLead(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param Opportunity $opportunity
     */
    public function setOpportunity(Opportunity $opportunity)
    {
        $this->opportunity = $opportunity;
    }

    /**
     * @return Opportunity
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }

    /**
     * @return ArrayCollection
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @param Call $call
     */
    public function addCall(Call $call)
    {
        if (!$this->hasCall($call)) {
            $this->getCalls()->add($call);
        }
    }

    /**
     * @param Call $call
     */
    public function removeCall(Call $call)
    {
        if ($this->hasCall($call)) {
            $this->getCalls()->removeElement($call);
        }
    }

    /**
     * @param Call $call
     *
     * @return bool
     */
    public function hasCall(Call $call)
    {
        return $this->getCalls()->contains($call);
    }

    /**
     * @return ArrayCollection
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param Email $email
     */
    public function addEmail(Email $email)
    {
        if (!$this->hasEmail($email)) {
            $this->getEmails()->add($email);
        }
    }

    /**
     * @param Email $email
     */
    public function removeEmail(Email $email)
    {
        if ($this->hasEmail($email)) {
            $this->getEmails()->removeElement($email);
        }
    }

    /**
     * @param Email $email
     *
     * @return bool
     */
    public function hasEmail(Email $email)
    {
        return $this->getEmails()->contains($email);
    }

    /**
     * @param WorkflowItem $workflowItem
     */
    public function setWorkflowItem(WorkflowItem $workflowItem)
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
    public function setWorkflowStep(WorkflowStep $workflowStep)
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
}
