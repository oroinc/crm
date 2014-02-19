<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\AddressBundle\Entity\AbstractPhone;

/**
 * @ORM\Entity
 * @ORM\Table("orocrm_contact_phone", indexes={
 *      @ORM\Index(name="primary_phone_idx", columns={"phone", "is_primary"})
 * })
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository")
 * @Config(
 *   defaultValues={
 *      "entity"={"icon"="icon-phone"}
 *  }
 * )
 */
class ContactPhone extends AbstractPhone
{
    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="phones")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
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
