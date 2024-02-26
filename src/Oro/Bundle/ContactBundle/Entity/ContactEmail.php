<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroContactBundle_Entity_ContactEmail;
use Oro\Bundle\AddressBundle\Entity\AbstractEmail;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Contact email entity
 * @mixin OroContactBundle_Entity_ContactEmail
 */
#[ORM\Entity]
#[ORM\Table('orocrm_contact_email')]
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
class ContactEmail extends AbstractEmail implements
    EmailInterface,
    ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'emails')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Contact $owner = null;

    /**
     * {@inheritdoc}
     */
    public function getEmailField()
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailOwner()
    {
        return $this->getOwner();
    }

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
