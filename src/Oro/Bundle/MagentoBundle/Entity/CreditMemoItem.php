<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\MagentoBundle\Model\ExtendCreditMemoItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="oro_magento_credit_memo_item",
 *     indexes={
 *          @ORM\Index(name="magecreditmemoitem_item_id_idx", columns={"item_id"})
 *     }
 * )
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
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
class CreditMemoItem extends ExtendCreditMemoItem implements IntegrationAwareInterface
{
    use IntegrationEntityTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="item_id", type="string", length=60, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $itemId;

    /**
     * @var CreditMemo
     *
     * @ORM\ManyToOne(targetEntity="CreditMemo", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var OrderItem
     *
     * @ORM\ManyToOne(targetEntity="OrderItem", cascade={"persist"})
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @var float
     *
     * @ORM\Column(name="weee_tax_applied_row_amount", type="money", nullable=true)
     */
    protected $weeeTaxAppliedRowAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price", type="money", nullable=true)
     */
    protected $basePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="base_weee_tax_row_disposition", type="money", nullable=true)
     */
    protected $baseWeeeTaxRowDisposition;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_amount", type="money", nullable=true)
     */
    protected $taxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_weee_tax_applied_amount", type="money", nullable=true)
     */
    protected $baseWeeeTaxAppliedAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="weee_tax_row_disposition", type="money", nullable=true)
     */
    protected $weeeTaxRowDisposition;

    /**
     * @var float
     *
     * @ORM\Column(name="base_row_total", type="money", nullable=true)
     */
    protected $baseRowTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_amount", type="money", nullable=true)
     */
    protected $discountAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="row_total", type="money", nullable=true)
     */
    protected $rowTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="weee_tax_applied_amount", type="money", nullable=true)
     */
    protected $weeeTaxAppliedAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_discount_amount", type="money", nullable=true)
     */
    protected $baseDiscountAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_weee_tax_disposition", type="money", nullable=true)
     */
    protected $baseWeeeTaxDisposition;

    /**
     * @var float
     *
     * @ORM\Column(name="price_incl_tax", type="money", nullable=true)
     */
    protected $priceInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="base_tax_amount", type="money", nullable=true)
     */
    protected $baseTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="weee_tax_disposition", type="money", nullable=true)
     */
    protected $weeeTaxDisposition;

    /**
     * @var float
     *
     * @ORM\Column(name="base_price_incl_tax", type="money", nullable=true)
     */
    protected $basePriceInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="qty", type="float", nullable=false)
     */
    protected $qty;

    /**
     * @var float
     *
     * @ORM\Column(name="base_cost", type="money", nullable=true)
     */
    protected $baseCost;

    /**
     * @var float
     *
     * @ORM\Column(name="base_weee_tax_app_row_amount", type="money", nullable=true)
     */
    protected $baseWeeeTaxAppliedRowAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var float
     *
     * @ORM\Column(name="base_row_total_incl_tax", type="money", nullable=true)
     */
    protected $baseRowTotalInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="row_total_incl_tax", type="money", nullable=true)
     */
    protected $rowTotalInclTax;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_data", type="string", length=255, nullable=true)
     */
    protected $additionalData;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var float
     *
     * @ORM\Column(name="weee_tax_applied", type="money", nullable=true)
     */
    protected $weeeTaxApplied;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var float
     *
     * @ORM\Column(name="hidden_tax_amount", type="money", nullable=true)
     */
    protected $hiddenTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_hidden_tax_amount", type="money", nullable=true)
     */
    protected $baseHiddenTaxAmount;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     *
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return CreditMemo
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param CreditMemo $parent
     *
     * @return $this
     */
    public function setParent(CreditMemo $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return $this
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->weeeTaxAppliedRowAmount;
    }

    /**
     * @param float $weeeTaxAppliedRowAmount
     *
     * @return $this
     */
    public function setWeeeTaxAppliedRowAmount($weeeTaxAppliedRowAmount)
    {
        $this->weeeTaxAppliedRowAmount = $weeeTaxAppliedRowAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * @param float $basePrice
     *
     * @return $this
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->baseWeeeTaxRowDisposition;
    }

    /**
     * @param float $baseWeeeTaxRowDisposition
     *
     * @return $this
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition)
    {
        $this->baseWeeeTaxRowDisposition = $baseWeeeTaxRowDisposition;

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
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->baseWeeeTaxAppliedAmount;
    }

    /**
     * @param float $baseWeeeTaxAppliedAmount
     *
     * @return $this
     */
    public function setBaseWeeeTaxAppliedAmount($baseWeeeTaxAppliedAmount)
    {
        $this->baseWeeeTaxAppliedAmount = $baseWeeeTaxAppliedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->weeeTaxRowDisposition;
    }

    /**
     * @param float $weeeTaxRowDisposition
     *
     * @return $this
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition)
    {
        $this->weeeTaxRowDisposition = $weeeTaxRowDisposition;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->baseRowTotal;
    }

    /**
     * @param float $baseRowTotal
     *
     * @return $this
     */
    public function setBaseRowTotal($baseRowTotal)
    {
        $this->baseRowTotal = $baseRowTotal;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param float $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

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
    public function getWeeeTaxAppliedAmount()
    {
        return $this->weeeTaxAppliedAmount;
    }

    /**
     * @param float $weeeTaxAppliedAmount
     *
     * @return $this
     */
    public function setWeeeTaxAppliedAmount($weeeTaxAppliedAmount)
    {
        $this->weeeTaxAppliedAmount = $weeeTaxAppliedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->baseDiscountAmount;
    }

    /**
     * @param float $baseDiscountAmount
     *
     * @return $this
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        $this->baseDiscountAmount = $baseDiscountAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->baseWeeeTaxDisposition;
    }

    /**
     * @param float $baseWeeeTaxDisposition
     *
     * @return $this
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition)
    {
        $this->baseWeeeTaxDisposition = $baseWeeeTaxDisposition;

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
    public function getBaseTaxAmount()
    {
        return $this->baseTaxAmount;
    }

    /**
     * @param float $baseTaxAmount
     *
     * @return $this
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        $this->baseTaxAmount = $baseTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getWeeeTaxDisposition()
    {
        return $this->weeeTaxDisposition;
    }

    /**
     * @param float $weeeTaxDisposition
     *
     * @return $this
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition)
    {
        $this->weeeTaxDisposition = $weeeTaxDisposition;

        return $this;
    }

    /**
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->basePriceInclTax;
    }

    /**
     * @param float $basePriceInclTax
     *
     * @return $this
     */
    public function setBasePriceInclTax($basePriceInclTax)
    {
        $this->basePriceInclTax = $basePriceInclTax;

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
     * @param float $qty
     *
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
    public function getBaseCost()
    {
        return $this->baseCost;
    }

    /**
     * @param float $baseCost
     *
     * @return $this
     */
    public function setBaseCost($baseCost)
    {
        $this->baseCost = $baseCost;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmount()
    {
        return $this->baseWeeeTaxAppliedRowAmount;
    }

    /**
     * @param float $baseWeeeTaxAppliedRowAmount
     *
     * @return $this
     */
    public function setBaseWeeeTaxAppliedRowAmount($baseWeeeTaxAppliedRowAmount)
    {
        $this->baseWeeeTaxAppliedRowAmount = $baseWeeeTaxAppliedRowAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->baseRowTotalInclTax;
    }

    /**
     * @param float $baseRowTotalInclTax
     *
     * @return $this
     */
    public function setBaseRowTotalInclTax($baseRowTotalInclTax)
    {
        $this->baseRowTotalInclTax = $baseRowTotalInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->rowTotalInclTax;
    }

    /**
     * @param float $rowTotalInclTax
     *
     * @return $this
     */
    public function setRowTotalInclTax($rowTotalInclTax)
    {
        $this->rowTotalInclTax = $rowTotalInclTax;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param string $additionalData
     *
     * @return $this
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;

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
     * @return float
     */
    public function getWeeeTaxApplied()
    {
        return $this->weeeTaxApplied;
    }

    /**
     * @param float $weeeTaxApplied
     *
     * @return $this
     */
    public function setWeeeTaxApplied($weeeTaxApplied)
    {
        $this->weeeTaxApplied = $weeeTaxApplied;

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
     * @param string $sku
     *
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->hiddenTaxAmount;
    }

    /**
     * @param float $hiddenTaxAmount
     *
     * @return $this
     */
    public function setHiddenTaxAmount($hiddenTaxAmount)
    {
        $this->hiddenTaxAmount = $hiddenTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->baseHiddenTaxAmount;
    }

    /**
     * @param float $baseHiddenTaxAmount
     *
     * @return $this
     */
    public function setBaseHiddenTaxAmount($baseHiddenTaxAmount)
    {
        $this->baseHiddenTaxAmount = $baseHiddenTaxAmount;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Organization $owner
     *
     * @return $this
     */
    public function setOwner(Organization $owner)
    {
        $this->owner = $owner;

        return $this;
    }
}
