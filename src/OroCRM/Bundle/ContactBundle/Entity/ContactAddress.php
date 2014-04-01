<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Table("orocrm_contact_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={"icon"="icon-map-marker"},
 *      }
 * )
 * @ORM\Entity
 */
class ContactAddress extends AbstractTypedAddress
{
    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="addresses",cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType",cascade={"persist"})
     * @ORM\JoinTable(
     *     name="orocrm_contact_adr_to_adr_type",
     *     joinColumns={@ORM\JoinColumn(name="contact_address_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
     * )
     * @Soap\ComplexType("string[]", nillable=true)
     **/
    protected $types;

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
