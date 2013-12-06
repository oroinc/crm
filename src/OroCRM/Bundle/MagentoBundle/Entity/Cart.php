<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseCart;

/**
 * Class Cart
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart", indexes={
 *      @ORM\Index(name="magecart_origin_idx", columns={"origin_id"})
 * })
 */
class Cart extends BaseCart
{
    /**
     * @var CartItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CartItem",
     *     mappedBy="cart", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"originId" = "DESC"})
     */
    protected $cartItems;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="carts",cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $customer;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Store", cascade="PERSIST")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $store;

    /**
     * Mage cart origin id (quote_id)
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer", options={"unsigned"=true})
     */
    protected $originId;

    /**
     * Total items qty
     * @var integer
     *
     * @ORM\Column(name="items_qty", type="integer", options={"unsigned"=true})
     */
    protected $itemsQty;

    /**
     * Items qty
     * @var integer
     *
     * @ORM\Column(name="items_count", type="integer", options={"unsigned"=true})
     */
    protected $itemsCount;

    /**
     * @var string
     *
     * @ORM\Column(name="base_currency_code", type="string", length=32, nullable=false)
     */
    protected $baseCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="store_currency_code", type="string", length=32, nullable=false)
     */
    protected $storeCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="quote_currency_code", type="string", length=32, nullable=false)
     */
    protected $quoteCurrencyCode;

    /**
     * @var float
     *
     * @ORM\Column(name="store_to_base_rate", type="decimal", nullable=false)
     */
    protected $storeToBaseRate;

    /**
     * @var float
     *
     * @ORM\Column(name="store_to_quote_rate", type="decimal", nullable=true)
     */
    protected $storeToQuoteRate;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="gift_message", type="string", length=255)
     */
    protected $giftMessage;

    /**
     * @var float
     *
     * @ORM\Column(name="is_guest", type="boolean")
     */
    protected $isGuest;

    /**
     * @return CartItem[]|Collection
     */
    public function getCartItems()
    {
        return $this->cartItems;
    }

    /**
     * @param \OroCRM\Bundle\MagentoBundle\Entity\Store $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return \OroCRM\Bundle\MagentoBundle\Entity\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @return int
     */
    public function getItemsQty()
    {
        return $this->itemsQty;
    }
}
