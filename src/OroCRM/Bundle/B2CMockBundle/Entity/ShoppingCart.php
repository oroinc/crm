<?php

namespace OroCRM\Bundle\B2CMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @ORM\Table("orocrm_b2c_shopping_cart")
 * @ORM\Entity
 */
class ShoppingCart
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $customer;

    /**
     * @var SaleAddress
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\B2CMockBundle\Entity\SaleAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $billingAddress;

    /**
     * @var SaleAddress
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\B2CMockBundle\Entity\SaleAddress", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="shipping_address_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $shippingAddress;

    /**
     * @var ShoppingCartStatus
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\B2CMockBundle\Entity\ShoppingCartStatus")
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $status;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Customer $customer
     * @return ShoppingCart
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param SaleAddress $billingAddress
     * @return ShoppingCart
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return SaleAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param SaleAddress $shippingAddress
     * @return ShoppingCart
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return SaleAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param ShoppingCartStatus $status
     * @return ShoppingCart
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return ShoppingCartStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
