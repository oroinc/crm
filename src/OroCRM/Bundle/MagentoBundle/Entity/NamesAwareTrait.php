<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

/**
 * Class NamesAwareTrait
 *
 * @package Oro\Bundle\MagentoBundle\Entity
 *
 * Denormalized data to process billing address person names for guest carts/orders
 * Or customer first/last name for logged in customer's cart
 * Only for internal use on grid
 */
trait NamesAwareTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * Update denormalized names based on current cart state
     * See doc block for firstName property
     */
    protected function updateNames()
    {
        $firstName = $lastName = null;
        $billingAddress = $this->getBillingAddress();

        if (null !== $this->getCustomer()) {
            $firstName = $this->getCustomer()->getFirstName();
            $lastName  = $this->getCustomer()->getLastName();
        } elseif (!empty($billingAddress)) {
            $firstName = $billingAddress->getFirstName();
            $lastName  = $billingAddress->getLastName();
        }

        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    /**
     * @return Customer
     */
    abstract public function getCustomer();

    /**
     * @return AbstractAddress
     */
    abstract public function getBillingAddress();

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
