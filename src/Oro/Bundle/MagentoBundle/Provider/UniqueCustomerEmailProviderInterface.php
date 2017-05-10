<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

interface UniqueCustomerEmailProviderInterface
{
    /**
     * @param SoapTransport $transport
     * @param Customer      $customer
     *
     * @return bool
     */
    public function isCustomerHasUniqueEmail(SoapTransport $transport, Customer $customer);
}
