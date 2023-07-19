<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactUsBundle_Entity_ContactRequest;
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
 * @mixin OroContactUsBundle_Entity_ContactRequest
 */
class ContactRequest extends AbstractContactRequest implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    const CONTACT_METHOD_BOTH  = 'oro.contactus.contactrequest.method.both';
    const CONTACT_METHOD_PHONE = 'oro.contactus.contactrequest.method.phone';
    const CONTACT_METHOD_EMAIL = 'oro.contactus.contactrequest.method.email';

    /**
     * @ORM\Column(name="customer_name", type="string", nullable=true)
     */
    protected ?string $customerName = null;

    /**
     * @ORM\Column(name="preferred_contact_method", type="string", length=100)
     */
    protected string $preferredContactMethod = self::CONTACT_METHOD_EMAIL;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactUsBundle\Entity\ContactReason")
     * @ORM\JoinColumn(name="contact_reason_id", referencedColumnName="id", nullable=true)
     **/
    protected ?ContactReason $contactReason = null;

    /**
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    protected ?string $feedback = null;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?Opportunity $opportunity = null;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\SalesBundle\Entity\Lead")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?Lead $lead = null;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected ?Organization $owner = null;

    /**
     * @param string $customerName
     */
    public function setCustomerName(string $customerName): void
    {
        $this->customerName = $customerName;
    }

    /**
     * @return string|null
     */
    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    /**
     * @param string $preferredContactMethod
     */
    public function setPreferredContactMethod(string $preferredContactMethod): void
    {
        $this->preferredContactMethod = $preferredContactMethod;
    }

    /**
     * @return string
     */
    public function getPreferredContactMethod(): string
    {
        return $this->preferredContactMethod;
    }

    /**
     * @param ContactReason|null $contactReason
     */
    public function setContactReason(?ContactReason $contactReason = null): void
    {
        $this->contactReason = $contactReason;
    }

    /**
     * @return ContactReason|null
     */
    public function getContactReason(): ?ContactReason
    {
        return $this->contactReason;
    }

    /**
     * @param string $feedback
     */
    public function setFeedback(?string $feedback): void
    {
        $this->feedback = $feedback;
    }

    /**
     * @return string|null
     */
    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setLead(?Lead $lead): void
    {
        $this->lead = $lead;
    }

    /**
     * @return Lead|null
     */
    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setOpportunity(?Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    /**
     * @return Opportunity|null
     */
    public function getOpportunity(): ?Opportunity
    {
        return $this->opportunity;
    }

    /**
     * @return Organization|null
     */
    public function getOwner(): ?Organization
    {
        return $this->owner;
    }

    public function setOwner(?Organization $organization): void
    {
        $this->owner = $organization;
    }
}
