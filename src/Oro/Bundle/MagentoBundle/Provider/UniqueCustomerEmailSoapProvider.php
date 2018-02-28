<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class UniqueCustomerEmailSoapProvider
{
    /**
     * @param MagentoSoapTransportInterface $transport
     * @param Customer      $customer
     *
     * @return bool
     */
    public function isCustomerHasUniqueEmail(MagentoSoapTransportInterface $transport, Customer $customer)
    {
        $filters = $this->getPreparedFilters($customer);
        $customers = $this->doRequest($transport, $filters);

        if (false === $customers) {
            return true;
        }

        $filteredCustomer = array_filter(
            $customers,
            function ($customerData) use ($customer) {
                if (is_object($customerData)) {
                    $customerData = (array)$customerData;
                }
                if ($customerData
                    && !empty($customerData['customer_id'])
                    && $customerData['customer_id'] == $customer->getOriginId()
                ) {
                    return false;
                }

                return true;
            }
        );

        return 0 === count($filteredCustomer);
    }

    /**
     * @param MagentoSoapTransportInterface $transport
     * @param array         $filters
     *
     * @return array | false
     */
    protected function doRequest(MagentoSoapTransportInterface $transport, array $filters)
    {
        $customers = $transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);

        if (is_array($customers)) {
            return $customers;
        }

        $customers = (array) $customers;
        if (empty($customers)) {
            return false;
        }

        return [$customers];
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    protected function getPreparedFilters(Customer $customer)
    {
        $filter = new BatchFilterBag();
        $filter->addComplexFilter(
            'email',
            [
                'key' => 'email',
                'value' => [
                    'key' => 'eq',
                    'value' => $customer->getEmail()
                ]
            ]
        );
        $filter->addComplexFilter(
            'store_id',
            [
                'key' => 'store_id',
                'value' => [
                    'key' => 'eq',
                    'value' => $customer->getStore()->getId()
                ]
            ]
        );

        return $filter->getAppliedFilters();
    }
}
