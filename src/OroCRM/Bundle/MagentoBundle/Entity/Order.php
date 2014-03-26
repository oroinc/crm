<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseOrder;

use OroCRM\Bundle\CallBundle\Entity\Call;

/**
 * Class Order
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="orocrm_magento_order",
 *     indexes={
 *          @ORM\Index(name="mageorder_created_idx",columns={"created_at"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_increment_id_channel_id", columns={"increment_id", "channel_id"})
 *     }
 * )
 * @Config(
 *  routeView="orocrm_magento_order_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-list-alt"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "workflow"={
 *          "active_workflow"="b2c_flow_order_follow_up"
 *      }
 *  }
 * )
 */
class Order extends BaseOrder
{
    use IntegrationEntityTrait, NamesAwareTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="increment_id", type="string", length=60, nullable=false)
     */
    protected $incrementId;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Customer", cascade={"persist"}, inversedBy="orders")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $customer;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrderAddress",
     *     mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
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
     * @ORM\Column(name="is_virtual", type="boolean", nullable=true)
     */
    protected $isVirtual = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_guest", type="boolean", nullable=true)
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
     * @ORM\Column(name="store_name", type="string", length=255, nullable=true)
     */
    protected $storeName;

    /**
     * @var float
     *
     * @ORM\Column(name="total_paid_amount", type="float", nullable=true)
     */
    protected $totalPaidAmount = 0;

    /**
     * @var double
     *
     * @ORM\Column(name="total_invoiced_amount", type="money", nullable=true)
     */
    protected $totalInvoicedAmount = 0;

    /**
     * @var double
     *
     * @ORM\Column(name="total_refunded_amount", type="money", nullable=true)
     */
    protected $totalRefundedAmount = 0;

    /**
     * @var double
     *
     * @ORM\Column(name="total_canceled_amount", type="money", nullable=true)
     */
    protected $totalCanceledAmount = 0;

    /**
     * @ORM\OneToOne(targetEntity="Cart", cascade={"persist"})
     */
    protected $cart;

    /**
     * @var OrderItem
     *
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order",cascade={"all"})
     */
    protected $items;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\CallBundle\Entity\Call")
     * @ORM\JoinTable(name="orocrm_magento_order_calls",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="call_id", referencedColumnName="id")}
     * )
     */
    protected $relatedCalls;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\EmailBundle\Entity\Email")
     * @ORM\JoinTable(name="orocrm_magento_order_emails",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="email_id", referencedColumnName="id")}
     * )
     */
    protected $relatedEmails;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    protected $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    protected $feedback;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @param WorkflowItem $workflowItem
     *
     * @return Order
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowStep
     *
     * @return Order
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    public function __construct()
    {
        $this->relatedCalls  = new ArrayCollection();
        $this->relatedEmails = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedCalls()
    {
        return $this->relatedCalls;
    }

    /**
     * @param Call $call
     *
     * @return Order
     */
    public function addRelatedCall(Call $call)
    {
        if (!$this->hasRelatedCall($call)) {
            $this->getRelatedCalls()->add($call);
        }

        return $this;
    }

    /**
     * @param Call $call
     *
     * @return Order
     */
    public function removeRelatedCall(Call $call)
    {
        if ($this->hasRelatedCall($call)) {
            $this->getRelatedCalls()->removeElement($call);
        }

        return $this;
    }

    /**
     * @param Call $call
     *
     * @return bool
     */
    public function hasRelatedCall(Call $call)
    {
        return $this->getRelatedCalls()->contains($call);
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedEmails()
    {
        return $this->relatedEmails;
    }

    /**
     * @param Email $email
     *
     * @return Order
     */
    public function addRelatedEmail(Email $email)
    {
        if (!$this->hasRelatedEmail($email)) {
            $this->getRelatedEmails()->add($email);
        }

        return $this;
    }

    /**
     * @param Email $email
     *
     * @return Order
     */
    public function removeRelatedEmail(Email $email)
    {
        if ($this->hasRelatedEmail($email)) {
            $this->getRelatedEmails()->removeElement($email);
        }

        return $this;
    }

    /**
     * @param Email $email
     *
     * @return bool
     */
    public function hasRelatedEmail(Email $email)
    {
        return $this->getRelatedEmails()->contains($email);
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
    public function getIncrementId()
    {
        return $this->incrementId;
    }

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

    /**
     * @param Cart $cart
     *
     * @return $this
     */
    public function setCart($cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param string $notes
     *
     * @return Order
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $feedback
     *
     * @return Order
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->updateNames();
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updateNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        $addresses = $this->getAddresses()->filter(
            function (AbstractTypedAddress $address) {
                return $address->hasTypeWithName(AddressType::TYPE_BILLING);
            }
        );

        return $addresses->first();
    }
}
