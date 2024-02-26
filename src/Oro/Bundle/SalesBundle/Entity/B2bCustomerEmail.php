<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroSalesBundle_Entity_B2bCustomerEmail;
use Oro\Bundle\AddressBundle\Entity\AbstractEmail;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Entity holds one email of Business Customer. Will be used in collection of emails and can be marked as primary.
 *
 * @mixin OroSalesBundle_Entity_B2bCustomerEmail
 */
#[ORM\Entity]
#[ORM\Table('orocrm_sales_b2bcustomer_email')]
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
class B2bCustomerEmail extends AbstractEmail implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: B2bCustomer::class, inversedBy: 'emails')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?B2bCustomer $owner = null;

    public function setOwner(B2bCustomer $owner = null)
    {
        $this->owner = $owner;
        $this->owner->addEmail($this);
    }

    /**
     * @return B2bCustomer
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
