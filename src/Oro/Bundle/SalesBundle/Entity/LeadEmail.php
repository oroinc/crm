<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_LeadEmail;
use Oro\Bundle\AddressBundle\Entity\AbstractEmail;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Lead email entity
 * @mixin OroSalesBundle_Entity_LeadEmail
 */
#[ORM\Entity]
#[ORM\Table('orocrm_sales_lead_email')]
#[ORM\Index(columns: ['email', 'is_primary'], name: 'primary_email_idx')]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-envelope'],
        'comment' => ['immutable' => true],
        'activity' => ['immutable' => true],
        'attachment' => ['immutable' => true],
        'dataaudit' => ['auditable' => true]
    ]
)]
class LeadEmail extends AbstractEmail implements ExtendEntityInterface, EmailInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Lead::class, inversedBy: 'emails')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Lead $owner = null;

    /**
     * Set lead as owner.
     */
    public function setOwner(Lead $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner lead.
     *
     * @return Lead
     */
    public function getOwner()
    {
        return $this->owner;
    }

    #[\Override]
    public function getEmailField()
    {
        return 'email';
    }

    #[\Override]
    public function getEmailOwner()
    {
        return $this->getOwner();
    }
}
