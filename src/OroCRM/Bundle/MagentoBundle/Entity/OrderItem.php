<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\MagentoBundle\Model\ExtendOrderItem;

/**
 * @ORM\Table("orocrm_magento_order_items")
 * @ORM\Entity
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt",
 *              "category"="magento"
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
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
class OrderItem extends ExtendOrderItem implements IntegrationAwareInterface, OriginAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items",cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="product_type", type="string", length=255, nullable=true)
     */
    protected $productType;

    /**
     * @var string
     *
     * @ORM\Column(name="product_options", type="text", nullable=true)
     */
    protected $productOptions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_virtual", type="boolean", nullable=true)
     */
    protected $isVirtual;

    /**
     * @var double
     *
     * @ORM\Column(name="original_price", type="money", nullable=true)
     */
    protected $originalPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_percent", type="percent", nullable=true)
     */
    protected $discountPercent;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /** Do not needed in magento order item, because magento api does not bring it up */
    protected $cost;

    /**
     * @param float $originalPrice
     *
     * @return OrderItem
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * @param string $productOptions
     *
     * @return OrderItem
     */
    public function setProductOptions($productOptions)
    {
        $this->productOptions = $productOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductOptions()
    {
        return $this->productOptions;
    }

    /**
     * @param string $productType
     *
     * @return OrderItem
     */
    public function setProductType($productType)
    {
        $this->productType = $productType;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param boolean $isVirtual
     *
     * @return OrderItem
     */
    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsVirtual()
    {
        return $this->isVirtual;
    }

    /**
     * @param float $discountPercent
     *
     * @return OrderItem
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * Set owner
     *
     * @param Organization $owner
     *
     * @return OrderItem
     */
    public function setOwner(Organization $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
