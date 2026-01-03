<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactUsBundle_Entity_ContactRequest;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Entity are used to track contact with individuals who are requesting information.
 *
 *
 * @method null|CustomerUser getCustomerUser() This method is available only in OroCommerce.
 * @method void setCustomerUser(CustomerUser $customerUser) This method is available only in OroCommerce.
 * @mixin OroContactUsBundle_Entity_ContactRequest
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_contactus_request')]
#[ORM\Index(columns: ['created_at', 'id'], name: 'request_create_idx')]
#[Config(
    routeName: 'oro_contactus_request_index',
    routeView: 'oro_contactus_request_view',
    defaultValues: [
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id'
        ],
        'entity' => ['icon' => 'fa-envelope'],
        'security' => ['type' => 'ACL', 'permissions' => 'All', 'group_name' => '', 'category' => 'account_management'],
        'grid' => ['default' => 'orcrm-contact-requests-grid']
    ]
)]
class ContactRequest extends AbstractContactRequest implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    public const CONTACT_METHOD_BOTH  = 'oro.contactus.contactrequest.method.both';
    public const CONTACT_METHOD_PHONE = 'oro.contactus.contactrequest.method.phone';
    public const CONTACT_METHOD_EMAIL = 'oro.contactus.contactrequest.method.email';

    #[ORM\Column(name: 'customer_name', type: Types::STRING, nullable: true)]
    protected ?string $customerName = null;

    #[ORM\Column(name: 'preferred_contact_method', type: Types::STRING, length: 100)]
    protected ?string $preferredContactMethod = self::CONTACT_METHOD_EMAIL;

    #[ORM\ManyToOne(targetEntity: ContactReason::class)]
    #[ORM\JoinColumn(name: 'contact_reason_id', referencedColumnName: 'id', nullable: true)]
    protected ?ContactReason $contactReason = null;

    #[ORM\Column(name: 'feedback', type: Types::TEXT, nullable: true)]
    protected ?string $feedback = null;

    #[ORM\ManyToOne(targetEntity: Opportunity::class)]
    #[ORM\JoinColumn(name: 'opportunity_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Opportunity $opportunity = null;

    #[ORM\ManyToOne(targetEntity: Lead::class)]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Lead $lead = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Organization $owner = null;

    public function setCustomerName(?string $customerName = null): void
    {
        $this->customerName = $customerName;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setPreferredContactMethod(string $preferredContactMethod): void
    {
        $this->preferredContactMethod = $preferredContactMethod;
    }

    public function getPreferredContactMethod(): string
    {
        return $this->preferredContactMethod;
    }

    public function setContactReason(?ContactReason $contactReason = null): void
    {
        $this->contactReason = $contactReason;
    }

    public function getContactReason(): ?ContactReason
    {
        return $this->contactReason;
    }

    public function setFeedback(?string $feedback): void
    {
        $this->feedback = $feedback;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setLead(?Lead $lead): void
    {
        $this->lead = $lead;
    }

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setOpportunity(?Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    public function getOpportunity(): ?Opportunity
    {
        return $this->opportunity;
    }

    public function getOwner(): ?Organization
    {
        return $this->owner;
    }

    public function setOwner(?Organization $organization): void
    {
        $this->owner = $organization;
    }
}
