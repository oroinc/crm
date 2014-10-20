<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use OroCRM\Bundle\MagentoBundle\Model\ExtendOrderAddress;

/**
 * @ORM\Table("orocrm_magento_order_address")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="icon-map-marker"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class OrderAddress extends ExtendOrderAddress
{
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType")
     * @ORM\JoinTable(
     *     name="orocrm_magento_order_addr_type",
     *     joinColumns={@ORM\JoinColumn(name="order_address_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
     * )
     **/
    protected $types;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="addresses",cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     */
    protected $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * Unset no used fields from mapping
     * Name parts unused due to magento api does not bring it up
     */
    protected $label;
    protected $namePrefix;
    protected $middleName;
    protected $nameSuffix;
    protected $street2;
    protected $primary;
    protected $created;
    protected $updated;

    /**
     * @param Order $owner
     *
     * @return OrderAddress
     */
    public function setOwner(Order $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $fax
     *
     * @return OrderAddress
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $phone
     *
     * @return OrderAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
