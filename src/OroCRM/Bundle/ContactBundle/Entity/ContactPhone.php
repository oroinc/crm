<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use OroCRM\Bundle\ContactBundle\Model\ExtendContactPhone;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_contact_phone", indexes={
 *      @ORM\Index(name="primary_phone_idx", columns={"phone", "is_primary"}),
 *      @ORM\Index(name="phone_idx", columns={"phone"})
 * })
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-phone"
 *          },
 *          "note"={
 *              "immutable"=true
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
class ContactPhone extends ExtendContactPhone
{
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
