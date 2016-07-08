<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\MagentoBundle\Model\ExtendCartItem;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class CartItem
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart_item", indexes={
 *      @ORM\Index(name="magecartitem_origin_idx", columns={"origin_id"}),
 *      @ORM\Index(name="magecartitem_sku_idx", columns={"sku"}),*
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-shopping-cart"
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
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
class CartItem extends ExtendCartItem implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="cartItems",cascade={"persist"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $cart;

    /**
     * Mage product id
     * @var integer
     *
     * @ORM\Column(name="product_id", type="integer", options={"unsigned"=true})
     */
    protected $productId;

    /**
     * Mage cart parent item id
     * @var integer
     *
     * @ORM\Column(name="parent_item_id", type="integer", options={"unsigned"=true}, nullable=true)
     */
    protected $parentItemId;

    /**
     * @var string
     *
     * @ORM\Column(name="free_shipping", type="string", length=255)
     */
    protected $freeShipping;

    /**
     * @var string
     *
     * @ORM\Column(name="gift_message", type="string", length=255, nullable=true)
     */
    protected $giftMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_class_id", type="string", length=255, nullable=true)
     */
    protected $taxClassId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var float
     *
     * @ORM\Column(name="is_virtual", type="boolean")
     */
    protected $isVirtual;

    /**
     * @var double
     *
     * @ORM\Column(name="custom_price", type="money", nullable=true)
     */
    protected $customPrice;

    /**
     * @var double
     *
     * @ORM\Column(name="price_incl_tax", type="money", nullable=true)
     */
    protected $priceInclTax;

    /**
     * @var double
     *
     * @ORM\Column(name="row_total", type="money")
     */
    protected $rowTotal;

    /**
     * @var double
     *
     * @ORM\Column(name="tax_amount", type="money")
     */
    protected $taxAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="product_type", type="string", length=255)
     */
    protected $productType;

    /**
     * @var string
     *
     * @ORM\Column(name="product_image_url", type="text", nullable=true)
     */
    protected $productImageUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="product_url", type="text", nullable=true)
     */
    protected $productUrl;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_removed", type="boolean", options={"default"=false})
     */
    protected $removed = false;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @param float $customPrice
     *
     * @return $this
     */
    public function setCustomPrice($customPrice)
    {
        $this->customPrice = $customPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getCustomPrice()
    {
        return $this->customPrice;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $freeShipping
     *
     * @return $this
     */
    public function setFreeShipping($freeShipping)
    {
        $this->freeShipping = $freeShipping;

        return $this;
    }

    /**
     * @return string
     */
    public function getFreeShipping()
    {
        return $this->freeShipping;
    }

    /**
     * @param string $giftMessage
     *
     * @return $this
     */
    public function setGiftMessage($giftMessage)
    {
        $this->giftMessage = $giftMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getGiftMessage()
    {
        return $this->giftMessage;
    }

    /**
     * @param float $isVirtual
     *
     * @return $this
     */
    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return float
     */
    public function getIsVirtual()
    {
        return $this->isVirtual;
    }

    /**
     * @param int $parentItemId
     *
     * @return $this
     */
    public function setParentItemId($parentItemId)
    {
        $this->parentItemId = $parentItemId;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentItemId()
    {
        return $this->parentItemId;
    }

    /**
     * @param float $priceInclTax
     *
     * @return $this
     */
    public function setPriceInclTax($priceInclTax)
    {
        $this->priceInclTax = $priceInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->priceInclTax;
    }

    /**
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param string $productType
     *
     * @return $this
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
     * @param float $rowTotal
     *
     * @return $this
     */
    public function setRowTotal($rowTotal)
    {
        $this->rowTotal = $rowTotal;

        return $this;
    }

    /**
     * @return float
     */
    public function getRowTotal()
    {
        return $this->rowTotal;
    }

    /**
     * @param float $taxAmount
     *
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param string $taxClassId
     *
     * @return $this
     */
    public function setTaxClassId($taxClassId)
    {
        $this->taxClassId = $taxClassId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaxClassId()
    {
        return $this->taxClassId;
    }

    /**
     * @return string
     */
    public function getProductImageUrl()
    {
        return $this->productImageUrl;
    }

    /**
     * @param string $productImageUrl
     *
     * @return $this
     */
    public function setProductImageUrl($productImageUrl)
    {
        $this->productImageUrl = $productImageUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return $this->productUrl;
    }

    /**
     * @param string $productUrl
     *
     * @return $this
     */
    public function setProductUrl($productUrl)
    {
        $this->productUrl = $productUrl;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemoved()
    {
        return (bool)$this->removed;
    }

    /**
     * @param boolean $removed
     *
     * @return $this
     */
    public function setRemoved($removed)
    {
        $this->removed = (bool)$removed;

        return $this;
    }

    /**
     * Set owner
     *
     * @param Organization $owner
     *
     * @return $this
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
