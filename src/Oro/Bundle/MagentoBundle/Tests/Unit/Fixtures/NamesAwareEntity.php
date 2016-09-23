<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NamesAwareTrait;

class NamesAwareEntity
{
    use NamesAwareTrait;

    protected $customer;
    protected $billingAddress;

    public function __construct($billingAddress = null, $customer = null)
    {
        $this->billingAddress = $billingAddress;
        $this->customer       = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return AbstractAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Do names update
     */
    public function doUpdate()
    {
        $this->updateNames();
    }
}
