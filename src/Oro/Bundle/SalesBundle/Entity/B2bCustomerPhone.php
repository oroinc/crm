<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_B2bCustomerPhone;
use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Entity holds one phone of Business Customer. Will be used in collection of phones and can be marked as primary.
 *
 * @mixin OroSalesBundle_Entity_B2bCustomerPhone
 */
#[ORM\Entity]
#[ORM\Table('orocrm_sales_b2bcustomer_phone')]
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
class B2bCustomerPhone extends AbstractPhone implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: B2bCustomer::class, inversedBy: 'phones')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?B2bCustomer $owner = null;

    public function setOwner(?B2bCustomer $owner = null)
    {
        $this->owner = $owner;
        $this->owner->addPhone($this);
    }

    /**
     * @return B2bCustomer
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
