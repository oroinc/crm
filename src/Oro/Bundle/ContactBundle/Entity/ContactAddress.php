<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactBundle_Entity_ContactAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Contact address entity
 * @mixin OroContactBundle_Entity_ContactAddress
 */
#[ORM\Entity]
#[ORM\Table('orocrm_contact_address')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-map-marker'],
        'activity' => ['immutable' => true],
        'dataaudit' => ['auditable' => true],
        'attachment' => ['immutable' => true]
    ]
)]
class ContactAddress extends AbstractTypedAddress implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Contact::class, cascade: ['persist'], inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?Contact $owner = null;

    /**
     * @var Collection<int, AddressType>
     **/
    #[ORM\ManyToMany(targetEntity: AddressType::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'orocrm_contact_adr_to_adr_type')]
    #[ORM\JoinColumn(name: 'contact_address_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'type_name', referencedColumnName: 'name')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 200, 'short' => true]])]
    protected ?Collection $types = null;

    /**
     * Set contact as owner.
     */
    public function setOwner(Contact $owner = null)
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
     * Get owner contact.
     *
     * @return Contact
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get address last update date/time
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
