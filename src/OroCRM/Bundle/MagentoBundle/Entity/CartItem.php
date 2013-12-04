<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CartItem
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_cart_item")
 */
class CartItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="cartItems",cascade={"persist"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $cart;

    /**
     * Mage cart item origin id (item_id)
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer", options={"unsigned"=true})
     */
    protected $originId;

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
     * @ORM\Column(name="sku", type="string", length=255)
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_data", type="text", nullable=true)
     */
    protected $additionalData;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", nullable=true)
     */
    protected $weight;

    /**
     * Qty
     * @var float
     *
     * @ORM\Column(name="qty", type="decimal")
     */
    protected $qty;

    /**
     * @var float
     *
     * @ORM\Column(name="is_virtual", type="boolean")
     */
    protected $isVirtual;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal")
     */
    protected $price;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price", type="decimal")
     */
    protected $basePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="custom_price", type="decimal", nullable=true)
     */
    protected $customPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="base_cost", type="decimal", nullable=true)
     */
    protected $baseCost;

    /**
     * @var float
     *
     * @ORM\Column(name="price_incl_tax", type="decimal")
     */
    protected $priceInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price_incl_tax", type="decimal")
     */
    protected $basePriceInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="row_total", type="decimal")
     */
    protected $rowTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="base_row_total", type="decimal")
     */
    protected $baseRowTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_percent", type="decimal")
     */
    protected $taxPercent;

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


    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $originId
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
