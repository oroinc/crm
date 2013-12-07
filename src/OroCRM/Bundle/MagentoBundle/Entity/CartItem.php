<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseCartItem;

/**
 * Class CartItem
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart_item", indexes={
 *      @ORM\Index(name="magecartitem_origin_idx", columns={"origin_id"}),
 *      @ORM\Index(name="magecartitem_sku_idx", columns={"sku"}),*
 * })
 */
class CartItem extends BaseCartItem
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
     * @ORM\Column(name="gift_message", type="string", length=255)
     */
    protected $giftMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_class_id", type="string", length=255)
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
     * @var float
     *
     * @ORM\Column(name="custom_price", type="decimal", nullable=true)
     */
    protected $customPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="price_incl_tax", type="decimal")
     */
    protected $priceInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="row_total", type="decimal")
     */
    protected $rowTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_amount", type="decimal")
     */
    protected $taxAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="product_type", type="string", length=255)
     */
    protected $productType;
}
