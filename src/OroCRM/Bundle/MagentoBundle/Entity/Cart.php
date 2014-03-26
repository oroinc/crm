<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseCart;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="orocrm_magento_cart",
 *  indexes={
 *      @ORM\Index(name="magecart_origin_idx", columns={"origin_id"}),
 *      @ORM\Index(name="magecart_updated_idx",columns={"updatedAt"})
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unq_cart_origin_id_channel_id", columns={"origin_id", "channel_id"})
 *  }
 * )
 * @Config(
 *  routeView="orocrm_magento_cart_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-shopping-cart"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      },
 *      "workflow"={
 *          "active_workflow"="b2c_flow_abandoned_shopping_cart"
 *      }
 *  }
 * )
 */
class Cart extends BaseCart
{
    use IntegrationEntityTrait, OriginTrait, NamesAwareTrait;

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
     *
     * @var integer
     *
     * @ORM\Column(name="items_qty", type="integer", options={"unsigned"=true})
     */
    protected $itemsQty;

    /**
     * Items qty
     *
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
     * @ORM\Column(name="store_to_base_rate", type="float", nullable=false)
     */
    protected $storeToBaseRate;

    /**
     * @var float
     *
     * @ORM\Column(name="store_to_quote_rate", type="float", nullable=true)
     */
    protected $storeToQuoteRate;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="payment_details", type="string", length=255, nullable=true)
     */
    protected $paymentDetails;

    /**
     * @var CartStatus
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\CartStatus")
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $status;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $opportunity;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\CallBundle\Entity\Call")
     * @ORM\JoinTable(name="orocrm_magento_cart_calls",
     *      joinColumns={@ORM\JoinColumn(name="cart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="call_id", referencedColumnName="id")}
     * )
     */
    protected $relatedCalls;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\EmailBundle\Entity\Email")
     * @ORM\JoinTable(name="orocrm_magento_cart_emails",
     *      joinColumns={@ORM\JoinColumn(name="cart_id", referencedColumnName="id")},
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
     * @return Cart
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
     * @return Cart
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
        $this->status        = new CartStatus('open');
        $this->cartItems     = new ArrayCollection();
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
     * @return Cart
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
     * @return Cart
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
     * @return Cart
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
     * @return Cart
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
     * @return CartItem[]|Collection
     */
    public function getCartItems()
    {
        return $this->cartItems;
    }

    /**
     * @param CartItem[]|Collection $cartItems
     */
    public function setCartItems(Collection $cartItems)
    {
        $this->cartItems = $cartItems;
    }

    /**
     * @param Store $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     *
     * @return Cart
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @param CartAddress $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(CartAddress $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @param CartAddress $billingAddress
     *
     * @return $this
     */
    public function setBillingAddress(CartAddress $billingAddress = null)
    {
        $this->billingAddress = $billingAddress;

        return $this;
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

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Cart
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemsQty()
    {
        return $this->itemsQty;
    }

    /**
     * @param float $itemsQty
     *
     * @return Cart
     */
    public function setItemsQty($itemsQty)
    {
        $this->itemsQty = $itemsQty;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @return string
     */
    public function getQuoteCurrencyCode()
    {
        return $this->quoteCurrencyCode;
    }

    /**
     * @param string $quoteCurrencyCode
     *
     * @return Cart
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode)
    {
        $this->quoteCurrencyCode = $quoteCurrencyCode;

        return $this;
    }

    /**
     * @param string $paymentDetails
     */
    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * @return string
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    /**
     * @param CartStatus $status
     *
     * @return Cart
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CartStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $baseCurrencyCode
     *
     * @return Cart
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        $this->baseCurrencyCode = $baseCurrencyCode;

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
     * @param string $giftMessage
     *
     * @return Cart
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
     * @param float $isGuest
     *
     * @return Cart
     */
    public function setIsGuest($isGuest)
    {
        $this->isGuest = $isGuest;

        return $this;
    }

    /**
     * @return float
     */
    public function getIsGuest()
    {
        return $this->isGuest;
    }

    /**
     * @param int $itemsCount
     *
     * @return Cart
     */
    public function setItemsCount($itemsCount)
    {
        $this->itemsCount = $itemsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * @param string $storeCurrencyCode
     *
     * @return Cart
     */
    public function setStoreCurrencyCode($storeCurrencyCode)
    {
        $this->storeCurrencyCode = $storeCurrencyCode;

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
     * @param float $storeToBaseRate
     *
     * @return Cart
     */
    public function setStoreToBaseRate($storeToBaseRate)
    {
        $this->storeToBaseRate = $storeToBaseRate;

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
     * @param float $storeToQuoteRate
     *
     * @return Cart
     */
    public function setStoreToQuoteRate($storeToQuoteRate)
    {
        $this->storeToQuoteRate = $storeToQuoteRate;

        return $this;
    }

    /**
     * @return float
     */
    public function getStoreToQuoteRate()
    {
        return $this->storeToQuoteRate;
    }

    /**
     * @param Opportunity $opportunity
     *
     * @return Cart
     */
    public function setOpportunity($opportunity)
    {
        $this->opportunity = $opportunity;

        return $this;
    }

    /**
     * @return Opportunity
     */
    public function getOpportunity()
    {
        return $this->opportunity;
    }

    /**
     * @param string $notes
     *
     * @return Cart
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
}
