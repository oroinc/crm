<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\TypedAddress;

/**
 * @ORM\Table("orocrm_contact_address")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\AddressRepository")
 */
class ContactAddress extends TypedAddress
{
    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="multiAddress")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * Set owner.
     *
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner.
     *
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
