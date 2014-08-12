<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
class CartItem extends ExtendCartItem
{
    use OriginTrait;

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
     * @param float $customPrice
     *
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
     * @return CartItem
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
}
