<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactBundle_Entity_ContactPhone;
use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Contact phone entity
 * @mixin OroContactBundle_Entity_ContactPhone
 */
#[ORM\Entity(repositoryClass: ContactPhoneRepository::class)]
#[ORM\Table('orocrm_contact_phone')]
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
class ContactPhone extends AbstractPhone implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'phones')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Contact $owner = null;

    /**
     * Set contact as owner.
     */
    public function setOwner(Contact $owner = null)
    {
        $this->owner = $owner;
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
}
