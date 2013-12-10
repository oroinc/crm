<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseCart;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class Cart
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart",
 *  indexes={
 *      @ORM\Index(name="magecart_origin_idx", columns={"origin_id"})
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unq_origin_id_channel_id", columns={"origin_id", "channel_id"})
 *  }
 * )
 * @Config(
 *  routeName="orocrm_magento_cart_index",
 *  routeView="orocrm_magento_cart_view",
 *  defaultValues={
 *      "entity"={"label"="Magento Cart", "plural_label"="Magento Carts"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Cart extends BaseCart
{
    use IntegrationEntityTrait, OriginTrait;

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
     * @ORM\Column(name="gift_message", type="string", length=255, nullable=true)
     */
    protected $giftMessage;

    /**
     * @var float
     *
     * @ORM\Column(name="is_guest", type="boolean")
     */
    protected $isGuest;

    /**
     * @var CartAddress $shippingAddress
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CartAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $shippingAddress;

    /**
     * @var CartAddress $billingAddress
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CartAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $billingAddress;

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
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     *
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param CartAddress $shippingAddress
     */
    public function setShippingAddress(CartAddress $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @param CartAddress $billingAddress
     */
    public function setBillingAddress(CartAddress $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return CartAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return CartAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }
}
