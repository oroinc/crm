<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_LeadAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\FormBundle\Entity\PrimaryItem;

/**
 * Lead address entity
 * @mixin OroSalesBundle_Entity_LeadAddress
 */
#[ORM\Entity]
#[ORM\Table('orocrm_sales_lead_address')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-map-marker'],
        'activity' => ['immutable' => true],
        'attachment' => ['immutable' => true]
    ]
)]
class LeadAddress extends AbstractAddress implements PrimaryItem, ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Lead::class, cascade: ['persist'], inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Lead $owner = null;

    #[ORM\Column(name: 'is_primary', type: Types::BOOLEAN, nullable: true)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?bool $primary = null;

    public function __construct()
    {
        $this->primary = false;
    }

    /**
     * Set lead as owner.
     */
    public function setOwner(Lead $owner = null)
    {
        if (null === $owner && null !== $this->owner) {
            $this->owner->removeAddress($this);
        }
        $this->owner = $owner;
        if (null !== $this->owner) {
            $this->owner->addAddress($this);
        }
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

    /**
     * @param bool $primary
     * @return LeadAddress
     */
    #[\Override]
    public function setPrimary($primary)
    {
        $this->primary = (bool)$primary;

        return $this;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function isPrimary()
    {
        return (bool)$this->primary;
    }
}
