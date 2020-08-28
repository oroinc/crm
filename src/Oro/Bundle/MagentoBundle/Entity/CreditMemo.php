<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\MagentoBundle\Model\ExtendCreditMemo;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents a credit memo.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="orocrm_magento_credit_memo",
 *     indexes={
 *          @ORM\Index(name="magecreditmemo_created_idx", columns={"created_at", "id"}),
 *          @ORM\Index(name="magecreditmemo_updated_idx", columns={"updated_at", "id"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_mcm_increment_id_channel_id", columns={"increment_id", "channel_id"})
 *     }
 * )
 * @Config(
 *     routeView="oro_magento_credit_memo_view",
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
 *          },
 *          "grid"={
 *              "default"="magento-credit-memo-grid",
 *          }
 *     }
 * )
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreditMemo extends ExtendCreditMemo implements
    ChannelAwareInterface,
    IntegrationAwareInterface,
    OriginAwareInterface
{
    use IntegrationEntityTrait, ChannelEntityTrait, OriginTrait;

    const STATUS_ENUM_CODE = 'creditmemo_status';

    /** constant for enum creditmemo_type */
    const STATUS_OPEN      = 1;
    const STATUS_REFUNDED  = 2;
    const STATUS_CANCELED  = 3;

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
     * @var int
     *
     * @ORM\Column(name="invoice_id", type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $invoiceId;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=255, nullable=true)
     */
    protected $transactionId;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="creditMemos", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @var Collection|CreditMemoItem[]
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
     * @var boolean
     *
     * @ORM\Column(name="is_email_sent", type="boolean", nullable=true)
     */
    protected $emailSent;

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
     * @ORM\Column(name="adjustment_negative", type="money", nullable=true)
     */
    protected $adjustmentNegative;

    /**
     * @var float
     *
     * @ORM\Column(name="shipping_amount", type="money", nullable=true)
     */
    protected $shippingAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="grand_total", type="money", nullable=true)
     */
    protected $grandTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment_positive", type="money", nullable=true)
     */
    protected $adjustmentPositive;

    /**
     * @var float
     *
     * @ORM\Column(name="customer_bal_total_refunded", type="float", nullable=true)
     */
    protected $customerBalTotalRefunded;

    /**
     * @var float
     *
     * @ORM\Column(name="reward_points_balance_refund", type="float", nullable=true)
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="imported_at", nullable=true)
     */
    protected $importedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="synced_at", nullable=true)
     */
    protected $syncedAt;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->items = new ArrayCollection();
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
     * @return int
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param int $invoiceId
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
     * @return Collection|CreditMemoItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Collection|CreditMemoItem[] $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param CreditMemoItem $item
     *
     * @return $this
     */
    public function addItem(CreditMemoItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    /**
     * @param CreditMemoItem $item
     *
     * @return $this
     */
    public function removeItem(CreditMemoItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

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

    /**
     * @return \DateTime
     */
    public function getSyncedAt()
    {
        return $this->syncedAt;
    }

    /**
     * @param \DateTime|null $syncedAt
     * @return $this
     */
    public function setSyncedAt(\DateTime $syncedAt = null)
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * @param \DateTime|null $importedAt
     * @return $this
     */
    public function setImportedAt(\DateTime $importedAt = null)
    {
        $this->importedAt = $importedAt;

        return $this;
    }
}
