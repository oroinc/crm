<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\MagentoBundle\Model\ExtendCreditMemoItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="orocrm_magento_creditmemo_item",
 *     indexes={
 *          @ORM\Index(name="magecreditmemoitem_origin_idx", columns={"origin_id"}),
 *          @ORM\Index(name="magecreditmemoitem_sku_idx", columns={"sku"})
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
class CreditMemoItem extends ExtendCreditMemoItem implements IntegrationAwareInterface, OriginAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CreditMemo
     *
     * @ORM\ManyToOne(targetEntity="CreditMemo", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="order_item_id", nullable=true, options={"unsigned"=true})
     */
    protected $orderItemId;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_amount", type="money", nullable=true)
     */
    protected $taxAmount;

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
     * @ORM\Column(name="qty", type="float", nullable=false)
     */
    protected $qty;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_data", type="text", nullable=true)
     */
    protected $additionalData;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

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
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * @param int $orderItemId
     *
     * @return $this
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;

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
