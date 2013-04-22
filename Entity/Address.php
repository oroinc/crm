<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table("oro_address")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Oro\Bundle\AddressBundle\Entity\Repository\AddressRepository")
 */
class Address extends AddressBase
{
    /**
     *  This inheritance needed to add possibility to store address in separate table
     *  http://docs.doctrine-project.org/en/latest/reference/inheritance-mapping.html
     */
}
