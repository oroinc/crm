<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractPhone;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Contact phone entity
 * @ORM\Entity
 * @ORM\Table("orocrm_contact_phone", indexes={
 *      @ORM\Index(name="primary_phone_idx", columns={"phone", "is_primary"}),
 *      @ORM\Index(name="phone_idx", columns={"phone"})
 * })
 * @ORM\Entity(repositoryClass="Oro\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-phone"
 *          },
 *          "comment"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          }
 *      }
 * )
 */
class ContactPhone extends AbstractPhone implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="phones")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * Set contact as owner.
     *
     * @param Contact $owner
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
