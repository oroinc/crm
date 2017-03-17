<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\MagentoBundle\Model\ExtendCreditMemo;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="oro_magento_credit_memo",
 *     indexes={
 *          @ORM\Index(name="magecreditmemo_created_idx", columns={"created_at", "id"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_mcm_increment_id_channel_id", columns={"increment_id", "channel_id"})
 *     }
 * )
 * @Config(
 *     defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
 *          }
 *     }
 * )
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CreditMemo extends ExtendCreditMemo implements
    ChannelAwareInterface,
    IntegrationAwareInterface
{
    use IntegrationEntityTrait, ChannelEntityTrait;

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
     * @ORM\Column(name="increment_id", type="string", length=60, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $incrementId;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_memo_id", type="string", length=60, nullable=true)
     */
    protected $creditMemoId;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_id", type="string", length=60, nullable=true)
     */
    protected $invoiceId;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=60, nullable=true)
     */
    protected $transactionId;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="creditMemos", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $order;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\MagentoBundle\Entity\Store")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=false
     *          }
     *      }
     * )
     */
    protected $store;

    /**
     * @var Collection|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrderAddress",
     *     mappedBy="creditMemo"
     * )
     */
    protected $addresses;

    /**
     * @var CreditMemoItem[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CreditMemoItem", mappedBy="parent", cascade={"all"})
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true
     *          }
     *      }
     * )
     */
    protected $items;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=32, nullable=true)
     */
    protected $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_email_sent", type="boolean", nullable=true)
     */
    protected $emailSent;

    /**
     * @var string
     *
     * @ORM\Column(name="global_currency_code", type="string", length=32, nullable=true)
     */
    protected $globalCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="base_currency_code", type="string", length=32, nullable=true)
     */
    protected $baseCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="order_currency_code", type="string", length=32, nullable=true)
     */
    protected $orderCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="store_currency_code", type="string", length=32, nullable=true)
     */
    protected $storeCurrencyCode;

    /**
     * @var string
     *
     * @ORM\Column(name="cybersource_token", type="string", length=255, nullable=true)
     */
    protected $cybersourceToken;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    protected $state;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_amount", type="money", nullable=true)
     */
    protected $taxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="shipping_tax_amount", type="money", nullable=true)
     */
    protected $shippingTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_tax_amount", type="money", nullable=true)
     */
    protected $baseTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_adjustment_positive", type="money", nullable=true)
     */
    protected $baseAdjustmentPositive;

    /**
     * @var float
     *
     * @ORM\Column(name="base_grand_total", type="money", nullable=true)
     */
    protected $baseGrandTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment", type="money", nullable=true)
     */
    protected $adjustment;

    /**
     * @var float
     *
     * @ORM\Column(name="subtotal", type="money", nullable=true)
     */
    protected $subtotal;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_amount", type="money", nullable=true)
     */
    protected $discountAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_subtotal", type="money", nullable=true)
     */
    protected $baseSubtotal;

    /**
     * @var float
     *
     * @ORM\Column(name="base_adjustment", type="money", nullable=true)
     */
    protected $baseAdjustment;

    /**
     * @var float
     *
     * @ORM\Column(name="base_to_global_rate", type="money", nullable=true)
     */
    protected $baseToGlobalRate;

    /**
     * @var float
     *
     * @ORM\Column(name="store_to_base_rate", type="money", nullable=true)
     */
    protected $storeToBaseRate;

    /**
     * @var float
     *
     * @ORM\Column(name="base_shipping_amount", type="money", nullable=true)
     */
    protected $baseShippingAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment_negative", type="money", nullable=true)
     */
    protected $adjustmentNegative;

    /**
     * @var float
     *
     * @ORM\Column(name="subtotal_incl_tax", type="money", nullable=true)
     */
    protected $subtotalInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="shipping_amount", type="money", nullable=true)
     */
    protected $shippingAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_subtotal_incl_tax", type="money", nullable=true)
     */
    protected $baseSubtotalInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="base_adjustment_negative", type="money", nullable=true)
     */
    protected $baseAdjustmentNegative;

    /**
     * @var float
     *
     * @ORM\Column(name="grand_total", type="money", nullable=true)
     */
    protected $grandTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="base_discount_amount", type="money", nullable=true)
     */
    protected $baseDiscountAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_to_order_rate", type="money", nullable=true)
     */
    protected $baseToOrderRate;

    /**
     * @var float
     *
     * @ORM\Column(name="store_to_order_rate", type="money", nullable=true)
     */
    protected $storeToOrderRate;

    /**
     * @var float
     *
     * @ORM\Column(name="base_shipping_tax_amount", type="money", nullable=true)
     */
    protected $baseShippingTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment_positive", type="money", nullable=true)
     */
    protected $adjustmentPositive;

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
     * @var float
     *
     * @ORM\Column(name="shipping_hidden_tax_amount", type="money", nullable=true)
     */
    protected $shippingHiddenTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_shipping_hidden_tax_amnt", type="money", nullable=true)
     */
    protected $baseShippingHiddenTaxAmnt;

    /**
     * @var float
     *
     * @ORM\Column(name="shipping_incl_tax", type="money", nullable=true)
     */
    protected $shippingInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="base_shipping_incl_tax", type="money", nullable=true)
     */
    protected $baseShippingInclTax;

    /**
     * @var float
     *
     * @ORM\Column(name="base_customer_balance_amount", type="money", nullable=true)
     */
    protected $baseCustomerBalanceAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="customer_balance_amount", type="money", nullable=true)
     */
    protected $customerBalanceAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="bs_customer_bal_total_refunded", type="money", nullable=true)
     */
    protected $bsCustomerBalTotalRefunded;

    /**
     * @var float
     *
     * @ORM\Column(name="customer_bal_total_refunded", type="money", nullable=true)
     */
    protected $customerBalTotalRefunded;

    /**
     * @var float
     *
     * @ORM\Column(name="base_gift_cards_amount", type="money", nullable=true)
     */
    protected $baseGiftCardsAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gift_cards_amount", type="money", nullable=true)
     */
    protected $giftCardsAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_base_price", type="money", nullable=true)
     */
    protected $gwBasePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_price", type="money", nullable=true)
     */
    protected $gwPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_items_base_price", type="money", nullable=true)
     */
    protected $gwItemsBasePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_items_price", type="money", nullable=true)
     */
    protected $gwItemsPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_card_base_price", type="money", nullable=true)
     */
    protected $gwCardBasePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_card_price", type="money", nullable=true)
     */
    protected $gwCardPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_base_tax_amount", type="money", nullable=true)
     */
    protected $gwBaseTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_tax_amount", type="money", nullable=true)
     */
    protected $gwTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_items_base_tax_amount", type="money", nullable=true)
     */
    protected $gwItemsBaseTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_items_tax_amount", type="money", nullable=true)
     */
    protected $gwItemsTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_card_base_tax_amount", type="money", nullable=true)
     */
    protected $gwCardBaseTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="gw_card_tax_amount", type="money", nullable=true)
     */
    protected $gwCardTaxAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="base_reward_currency_amount", type="money", nullable=true)
     */
    protected $baseRewardCurrencyAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="reward_currency_amount", type="money", nullable=true)
     */
    protected $rewardCurrencyAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="reward_points_balance", type="money", nullable=true)
     */
    protected $rewardPointsBalance;

    /**
     * @var float
     *
     * @ORM\Column(name="reward_points_balance_refund", type="money", nullable=true)
     */
    protected $rewardPointsBalanceRefund;

    /**
     * Date of updating
     *
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * Date of creation
     *
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->addresses = new ArrayCollection();
    }

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
    public function getIncrementId()
    {
        return $this->incrementId;
    }

    /**
     * @param string $incrementId
     *
     * @return $this
     */
    public function setIncrementId($incrementId)
    {
        $this->incrementId = $incrementId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreditMemoId()
    {
        return $this->creditMemoId;
    }

    /**
     * @param string $creditMemoId
     *
     * @return $this
     */
    public function setCreditMemoId($creditMemoId)
    {
        $this->creditMemoId = $creditMemoId;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string $invoiceId
     *
     * @return $this
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return $this
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmailSent()
    {
        return $this->emailSent;
    }

    /**
     * @param bool $emailSent
     *
     * @return $this
     */
    public function setEmailSent($emailSent)
    {
        $this->emailSent = $emailSent;

        return $this;
    }

    /**
     * @return string
     */
    public function getGlobalCurrencyCode()
    {
        return $this->globalCurrencyCode;
    }

    /**
     * @param string $globalCurrencyCode
     *
     * @return $this
     */
    public function setGlobalCurrencyCode($globalCurrencyCode)
    {
        $this->globalCurrencyCode = $globalCurrencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->baseCurrencyCode;
    }

    /**
     * @param string $baseCurrencyCode
     *
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        $this->baseCurrencyCode = $baseCurrencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderCurrencyCode()
    {
        return $this->orderCurrencyCode;
    }

    /**
     * @param string $orderCurrencyCode
     *
     * @return $this
     */
    public function setOrderCurrencyCode($orderCurrencyCode)
    {
        $this->orderCurrencyCode = $orderCurrencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return $this->storeCurrencyCode;
    }

    /**
     * @param string $storeCurrencyCode
     *
     * @return $this
     */
    public function setStoreCurrencyCode($storeCurrencyCode)
    {
        $this->storeCurrencyCode = $storeCurrencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCybersourceToken()
    {
        return $this->cybersourceToken;
    }

    /**
     * @param string $cybersourceToken
     *
     * @return $this
     */
    public function setCybersourceToken($cybersourceToken)
    {
        $this->cybersourceToken = $cybersourceToken;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param ArrayCollection|Collection $addresses
     *
     * @return $this
     */
    public function setAddresses(Collection $addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

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
    public function getShippingTaxAmount()
    {
        return $this->shippingTaxAmount;
    }

    /**
     * @param float $shippingTaxAmount
     *
     * @return $this
     */
    public function setShippingTaxAmount($shippingTaxAmount)
    {
        $this->shippingTaxAmount = $shippingTaxAmount;

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
    public function getBaseAdjustmentPositive()
    {
        return $this->baseAdjustmentPositive;
    }

    /**
     * @param float $baseAdjustmentPositive
     *
     * @return $this
     */
    public function setBaseAdjustmentPositive($baseAdjustmentPositive)
    {
        $this->baseAdjustmentPositive = $baseAdjustmentPositive;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseGrandTotal()
    {
        return $this->baseGrandTotal;
    }

    /**
     * @param float $baseGrandTotal
     *
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        $this->baseGrandTotal = $baseGrandTotal;

        return $this;
    }

    /**
     * @return float
     */
    public function getAdjustment()
    {
        return $this->adjustment;
    }

    /**
     * @param float $adjustment
     *
     * @return $this
     */
    public function setAdjustment($adjustment)
    {
        $this->adjustment = $adjustment;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @param float $subtotal
     *
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

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
    public function getBaseSubtotal()
    {
        return $this->baseSubtotal;
    }

    /**
     * @param float $baseSubtotal
     *
     * @return $this
     */
    public function setBaseSubtotal($baseSubtotal)
    {
        $this->baseSubtotal = $baseSubtotal;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAdjustment()
    {
        return $this->baseAdjustment;
    }

    /**
     * @param float $baseAdjustment
     *
     * @return $this
     */
    public function setBaseAdjustment($baseAdjustment)
    {
        $this->baseAdjustment = $baseAdjustment;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseToGlobalRate()
    {
        return $this->baseToGlobalRate;
    }

    /**
     * @param float $baseToGlobalRate
     *
     * @return $this
     */
    public function setBaseToGlobalRate($baseToGlobalRate)
    {
        $this->baseToGlobalRate = $baseToGlobalRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getStoreToBaseRate()
    {
        return $this->storeToBaseRate;
    }

    /**
     * @param float $storeToBaseRate
     *
     * @return $this
     */
    public function setStoreToBaseRate($storeToBaseRate)
    {
        $this->storeToBaseRate = $storeToBaseRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->baseShippingAmount;
    }

    /**
     * @param float $baseShippingAmount
     *
     * @return $this
     */
    public function setBaseShippingAmount($baseShippingAmount)
    {
        $this->baseShippingAmount = $baseShippingAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAdjustmentNegative()
    {
        return $this->adjustmentNegative;
    }

    /**
     * @param float $adjustmentNegative
     *
     * @return $this
     */
    public function setAdjustmentNegative($adjustmentNegative)
    {
        $this->adjustmentNegative = $adjustmentNegative;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubtotalInclTax()
    {
        return $this->subtotalInclTax;
    }

    /**
     * @param float $subtotalInclTax
     *
     * @return $this
     */
    public function setSubtotalInclTax($subtotalInclTax)
    {
        $this->subtotalInclTax = $subtotalInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param float $shippingAmount
     *
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->baseSubtotalInclTax;
    }

    /**
     * @param float $baseSubtotalInclTax
     *
     * @return $this
     */
    public function setBaseSubtotalInclTax($baseSubtotalInclTax)
    {
        $this->baseSubtotalInclTax = $baseSubtotalInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAdjustmentNegative()
    {
        return $this->baseAdjustmentNegative;
    }

    /**
     * @param float $baseAdjustmentNegative
     *
     * @return $this
     */
    public function setBaseAdjustmentNegative($baseAdjustmentNegative)
    {
        $this->baseAdjustmentNegative = $baseAdjustmentNegative;

        return $this;
    }

    /**
     * @return float
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * @param float $grandTotal
     *
     * @return $this
     */
    public function setGrandTotal($grandTotal)
    {
        $this->grandTotal = $grandTotal;

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
    public function getBaseToOrderRate()
    {
        return $this->baseToOrderRate;
    }

    /**
     * @param float $baseToOrderRate
     *
     * @return $this
     */
    public function setBaseToOrderRate($baseToOrderRate)
    {
        $this->baseToOrderRate = $baseToOrderRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getStoreToOrderRate()
    {
        return $this->storeToOrderRate;
    }

    /**
     * @param float $storeToOrderRate
     *
     * @return $this
     */
    public function setStoreToOrderRate($storeToOrderRate)
    {
        $this->storeToOrderRate = $storeToOrderRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->baseShippingTaxAmount;
    }

    /**
     * @param float $baseShippingTaxAmount
     *
     * @return $this
     */
    public function setBaseShippingTaxAmount($baseShippingTaxAmount)
    {
        $this->baseShippingTaxAmount = $baseShippingTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAdjustmentPositive()
    {
        return $this->adjustmentPositive;
    }

    /**
     * @param float $adjustmentPositive
     *
     * @return $this
     */
    public function setAdjustmentPositive($adjustmentPositive)
    {
        $this->adjustmentPositive = $adjustmentPositive;

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
     * @return float
     */
    public function getShippingHiddenTaxAmount()
    {
        return $this->shippingHiddenTaxAmount;
    }

    /**
     * @param float $shippingHiddenTaxAmount
     *
     * @return $this
     */
    public function setShippingHiddenTaxAmount($shippingHiddenTaxAmount)
    {
        $this->shippingHiddenTaxAmount = $shippingHiddenTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseShippingHiddenTaxAmnt()
    {
        return $this->baseShippingHiddenTaxAmnt;
    }

    /**
     * @param float $baseShippingHiddenTaxAmnt
     *
     * @return $this
     */
    public function setBaseShippingHiddenTaxAmnt($baseShippingHiddenTaxAmnt)
    {
        $this->baseShippingHiddenTaxAmnt = $baseShippingHiddenTaxAmnt;

        return $this;
    }

    /**
     * @return float
     */
    public function getShippingInclTax()
    {
        return $this->shippingInclTax;
    }

    /**
     * @param float $shippingInclTax
     *
     * @return $this
     */
    public function setShippingInclTax($shippingInclTax)
    {
        $this->shippingInclTax = $shippingInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseShippingInclTax()
    {
        return $this->baseShippingInclTax;
    }

    /**
     * @param float $baseShippingInclTax
     *
     * @return $this
     */
    public function setBaseShippingInclTax($baseShippingInclTax)
    {
        $this->baseShippingInclTax = $baseShippingInclTax;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseCustomerBalanceAmount()
    {
        return $this->baseCustomerBalanceAmount;
    }

    /**
     * @param float $baseCustomerBalanceAmount
     *
     * @return $this
     */
    public function setBaseCustomerBalanceAmount($baseCustomerBalanceAmount)
    {
        $this->baseCustomerBalanceAmount = $baseCustomerBalanceAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getCustomerBalanceAmount()
    {
        return $this->customerBalanceAmount;
    }

    /**
     * @param float $customerBalanceAmount
     *
     * @return $this
     */
    public function setCustomerBalanceAmount($customerBalanceAmount)
    {
        $this->customerBalanceAmount = $customerBalanceAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBsCustomerBalTotalRefunded()
    {
        return $this->bsCustomerBalTotalRefunded;
    }

    /**
     * @param float $bsCustomerBalTotalRefunded
     *
     * @return $this
     */
    public function setBsCustomerBalTotalRefunded($bsCustomerBalTotalRefunded)
    {
        $this->bsCustomerBalTotalRefunded = $bsCustomerBalTotalRefunded;

        return $this;
    }

    /**
     * @return float
     */
    public function getCustomerBalTotalRefunded()
    {
        return $this->customerBalTotalRefunded;
    }

    /**
     * @param float $customerBalTotalRefunded
     *
     * @return $this
     */
    public function setCustomerBalTotalRefunded($customerBalTotalRefunded)
    {
        $this->customerBalTotalRefunded = $customerBalTotalRefunded;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseGiftCardsAmount()
    {
        return $this->baseGiftCardsAmount;
    }

    /**
     * @param float $baseGiftCardsAmount
     *
     * @return $this
     */
    public function setBaseGiftCardsAmount($baseGiftCardsAmount)
    {
        $this->baseGiftCardsAmount = $baseGiftCardsAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGiftCardsAmount()
    {
        return $this->giftCardsAmount;
    }

    /**
     * @param float $giftCardsAmount
     *
     * @return $this
     */
    public function setGiftCardsAmount($giftCardsAmount)
    {
        $this->giftCardsAmount = $giftCardsAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwBasePrice()
    {
        return $this->gwBasePrice;
    }

    /**
     * @param float $gwBasePrice
     *
     * @return $this
     */
    public function setGwBasePrice($gwBasePrice)
    {
        $this->gwBasePrice = $gwBasePrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwPrice()
    {
        return $this->gwPrice;
    }

    /**
     * @param float $gwPrice
     *
     * @return $this
     */
    public function setGwPrice($gwPrice)
    {
        $this->gwPrice = $gwPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwItemsBasePrice()
    {
        return $this->gwItemsBasePrice;
    }

    /**
     * @param float $gwItemsBasePrice
     *
     * @return $this
     */
    public function setGwItemsBasePrice($gwItemsBasePrice)
    {
        $this->gwItemsBasePrice = $gwItemsBasePrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwItemsPrice()
    {
        return $this->gwItemsPrice;
    }

    /**
     * @param float $gwItemsPrice
     *
     * @return $this
     */
    public function setGwItemsPrice($gwItemsPrice)
    {
        $this->gwItemsPrice = $gwItemsPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwCardBasePrice()
    {
        return $this->gwCardBasePrice;
    }

    /**
     * @param float $gwCardBasePrice
     *
     * @return $this
     */
    public function setGwCardBasePrice($gwCardBasePrice)
    {
        $this->gwCardBasePrice = $gwCardBasePrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwCardPrice()
    {
        return $this->gwCardPrice;
    }

    /**
     * @param float $gwCardPrice
     *
     * @return $this
     */
    public function setGwCardPrice($gwCardPrice)
    {
        $this->gwCardPrice = $gwCardPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwBaseTaxAmount()
    {
        return $this->gwBaseTaxAmount;
    }

    /**
     * @param float $gwBaseTaxAmount
     *
     * @return $this
     */
    public function setGwBaseTaxAmount($gwBaseTaxAmount)
    {
        $this->gwBaseTaxAmount = $gwBaseTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwTaxAmount()
    {
        return $this->gwTaxAmount;
    }

    /**
     * @param float $gwTaxAmount
     *
     * @return $this
     */
    public function setGwTaxAmount($gwTaxAmount)
    {
        $this->gwTaxAmount = $gwTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwItemsBaseTaxAmount()
    {
        return $this->gwItemsBaseTaxAmount;
    }

    /**
     * @param float $gwItemsBaseTaxAmount
     *
     * @return $this
     */
    public function setGwItemsBaseTaxAmount($gwItemsBaseTaxAmount)
    {
        $this->gwItemsBaseTaxAmount = $gwItemsBaseTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwItemsTaxAmount()
    {
        return $this->gwItemsTaxAmount;
    }

    /**
     * @param float $gwItemsTaxAmount
     *
     * @return $this
     */
    public function setGwItemsTaxAmount($gwItemsTaxAmount)
    {
        $this->gwItemsTaxAmount = $gwItemsTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwCardBaseTaxAmount()
    {
        return $this->gwCardBaseTaxAmount;
    }

    /**
     * @param float $gwCardBaseTaxAmount
     *
     * @return $this
     */
    public function setGwCardBaseTaxAmount($gwCardBaseTaxAmount)
    {
        $this->gwCardBaseTaxAmount = $gwCardBaseTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getGwCardTaxAmount()
    {
        return $this->gwCardTaxAmount;
    }

    /**
     * @param float $gwCardTaxAmount
     *
     * @return $this
     */
    public function setGwCardTaxAmount($gwCardTaxAmount)
    {
        $this->gwCardTaxAmount = $gwCardTaxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseRewardCurrencyAmount()
    {
        return $this->baseRewardCurrencyAmount;
    }

    /**
     * @param float $baseRewardCurrencyAmount
     *
     * @return $this
     */
    public function setBaseRewardCurrencyAmount($baseRewardCurrencyAmount)
    {
        $this->baseRewardCurrencyAmount = $baseRewardCurrencyAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getRewardCurrencyAmount()
    {
        return $this->rewardCurrencyAmount;
    }

    /**
     * @param float $rewardCurrencyAmount
     *
     * @return $this
     */
    public function setRewardCurrencyAmount($rewardCurrencyAmount)
    {
        $this->rewardCurrencyAmount = $rewardCurrencyAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getRewardPointsBalance()
    {
        return $this->rewardPointsBalance;
    }

    /**
     * @param float $rewardPointsBalance
     *
     * @return $this
     */
    public function setRewardPointsBalance($rewardPointsBalance)
    {
        $this->rewardPointsBalance = $rewardPointsBalance;

        return $this;
    }

    /**
     * @return float
     */
    public function getRewardPointsBalanceRefund()
    {
        return $this->rewardPointsBalanceRefund;
    }

    /**
     * @param float $rewardPointsBalanceRefund
     *
     * @return $this
     */
    public function setRewardPointsBalanceRefund($rewardPointsBalanceRefund)
    {
        $this->rewardPointsBalanceRefund = $rewardPointsBalanceRefund;

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
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
