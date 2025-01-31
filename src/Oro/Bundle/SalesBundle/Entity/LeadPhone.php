<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_LeadPhone;
use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Lead phone entity
 * @mixin OroSalesBundle_Entity_LeadPhone
 */
#[ORM\Entity]
#[ORM\Table('orocrm_sales_lead_phone')]
#[ORM\Index(columns: ['phone', 'is_primary'], name: 'primary_phone_idx')]
#[ORM\Index(columns: ['phone'], name: 'phone_idx')]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-phone'],
        'comment' => ['immutable' => true],
        'activity' => ['immutable' => true],
        'attachment' => ['immutable' => true],
        'dataaudit' => ['auditable' => true]
    ]
)]
class LeadPhone extends AbstractPhone implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Lead::class, inversedBy: 'phones')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Lead $owner = null;

    /**
     * Set Lead as owner.
     */
    public function setOwner(?Lead $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner Lead.
     *
     * @return Lead
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
