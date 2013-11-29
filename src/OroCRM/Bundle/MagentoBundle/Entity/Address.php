<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

/**
 * @ORM\Table("orocrm_magento_customer_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Magento Customer Address", "plural_label"="Magento Customer Addresses"}
 *  }
 * )
 * @ORM\Entity
 * @Oro\Loggable
 */
class Address extends AbstractTypedAddress
{
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="addresses",cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType",cascade={"persist"})
     * @ORM\JoinTable(
     *     name="orocrm_magento_customer_address_to_address_type",
     *     joinColumns={@ORM\JoinColumn(name="contact_address_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
     * )
     **/
    protected $types;

    /**
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer")
     */
    protected $originId;

    /**
     * Set contact as owner.
     *
     * @param Customer $owner
     */
    public function setOwner(Customer $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner customer.
     *
     * @return Customer
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param int $originId
     *
     * @return $this
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }
}
