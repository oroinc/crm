<?php

namespace OroCRM\Bundle\B2CMockBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

/**
 * @ORM\Table("orocrm_b2c_sale_address")
 * @ORM\Entity
 */
class SaleAddress extends AbstractTypedAddress
{
    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="orocrm_b2c_sale_address_to_address_type",
     *     joinColumns={@ORM\JoinColumn(name="b2c_sale_address_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
     * )
     **/
    protected $types;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * Set email
     *
     * @param string $email
     * @return SaleOrder
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return SaleOrder
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
