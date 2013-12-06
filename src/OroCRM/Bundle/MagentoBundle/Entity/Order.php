<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseOrder;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class Order
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_order",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_original_id_channel_id", columns={"original_id", "channel_id"})
 *     }
 * )
 * @Config(
 *  routeName="orocrm_magento_order_index",
 *  routeView="orocrm_magento_order_view",
 *  defaultValues={
 *      "entity"={"label"="Magento Order", "plural_label"="Magento Orders"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Order extends BaseOrder
{
    use IntegrationEntityTrait;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $owner;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrderAddress",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
     */
    protected $addresses;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Store", cascade="PERSIST")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $store;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_virtual", type="boolean")
     */
    protected $isVirtual = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_guest", type="boolean")
     */
    protected $isGuest = false;

    /**
     * @var string
     *
     * @ORM\Column(name="gift_message", type="string", length=255, nullable=true)
     */
    protected $giftMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="remote_ip", type="string", length=255, nullable=true)
     */
    protected $remoteIp;

    /**
     * @var string
     *
     * @ORM\Column(name="store_name", type="string", length=255, nullable=false)
     */
    protected $storeName;

    /**
     * @var float
     *
     * @ORM\Column(name="total_paid_amount", type="float")
     */
    protected $totalPaidAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="total_invoiced_amount", type="float")
     */
    protected $totalInvoicedAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="total_refunded_amount", type="float")
     */
    protected $totalRefundedAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="total_canceled_amount", type="float")
     */
    protected $totalCanceledAmount = 0;

    /**
     * @TODO Add real cart here
     */
    protected $cart;

    /**
     * @var OrderItem
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order",cascade={"all"})
     */
    protected $items;

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
     * @param boolean $isGuest
     *
     * @return $this
     */
    public function setIsGuest($isGuest)
    {
        $this->isGuest = $isGuest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsGuest()
    {
        return $this->isGuest;
    }

    /**
     * @param boolean $isVirtual
     *
     * @return $this
     */
    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsVirtual()
    {
        return $this->isVirtual;
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
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param string $storeName
     *
     * @return $this
     */
    public function setStoreName($storeName)
    {
        $this->storeName = $storeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->storeName;
    }

    /**
     * @param float $totalCanceledAmount
     *
     * @return $this
     */
    public function setTotalCanceledAmount($totalCanceledAmount)
    {
        $this->totalCanceledAmount = $totalCanceledAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalCanceledAmount()
    {
        return $this->totalCanceledAmount;
    }

    /**
     * @param float $totalInvoicedAmount
     *
     * @return $this
     */
    public function setTotalInvoicedAmount($totalInvoicedAmount)
    {
        $this->totalInvoicedAmount = $totalInvoicedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInvoicedAmount()
    {
        return $this->totalInvoicedAmount;
    }

    /**
     * @param float $totalPaidAmount
     *
     * @return $this
     */
    public function setTotalPaidAmount($totalPaidAmount)
    {
        $this->totalPaidAmount = $totalPaidAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalPaidAmount()
    {
        return $this->totalPaidAmount;
    }

    /**
     * @param float $totalRefundedAmount
     *
     * @return $this
     */
    public function setTotalRefundedAmount($totalRefundedAmount)
    {
        $this->totalRefundedAmount = $totalRefundedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalRefundedAmount()
    {
        return $this->totalRefundedAmount;
    }

    /**
     * @param string $remoteIp
     *
     * @return $this
     */
    public function setRemoteIp($remoteIp)
    {
        $this->remoteIp = $remoteIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }
}
