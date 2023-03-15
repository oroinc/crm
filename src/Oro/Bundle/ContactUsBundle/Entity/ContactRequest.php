<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Entity are used to track contact with individuals who are requesting information.
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_contactus_request",
 *      indexes={@ORM\Index(name="request_create_idx",columns={"created_at", "id"})}
 * )
 *
 * @Config(
 *      routeName="oro_contactus_request_index",
 *      routeView="oro_contactus_request_view",
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "entity"={
 *              "icon"="fa-envelope"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="All",
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *          "grid"={
 *              "default"="orcrm-contact-requests-grid"
 *          }
 *      }
 * )
 * @codingStandardsIgnoreStart
 * @method null|\Oro\Bundle\CustomerBundle\Entity\CustomerUser getCustomerUser() This method is available only in OroCommerce.
 * @method void setCustomerUser(\Oro\Bundle\CustomerBundle\Entity\CustomerUser $customerUser) This method is available only in OroCommerce.
 * @codingStandardsIgnoreEnd
 */
class ContactRequest extends AbstractContactRequest implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    const CONTACT_METHOD_BOTH  = 'oro.contactus.contactrequest.method.both';
    const CONTACT_METHOD_PHONE = 'oro.contactus.contactrequest.method.phone';
    const CONTACT_METHOD_EMAIL = 'oro.contactus.contactrequest.method.email';

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
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactUsBundle\Entity\ContactReason")
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
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $opportunity;

    /**
     * @var Lead
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\SalesBundle\Entity\Lead")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $lead;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

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
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(Organization $organization)
    {
        $this->owner = $organization;
    }
}
