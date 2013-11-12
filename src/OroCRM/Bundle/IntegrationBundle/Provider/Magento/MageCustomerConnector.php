<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\AbstractConnector;

class MageCustomerConnector extends AbstractConnector
{
    /**
     * Get customer list
     *
     * @param array $filters
     * @return array
     */
    public function getCustomersList($filters = [])
    {
        return $this->call('customerCustomerList', $filters);
    }

    /**
     * @param $id
     * @param bool $isIncludeAddresses
     * @param array $onlyAttributes
     * @return mixed
     */
    public function getCustomerData($id, $isIncludeAddresses = false, $onlyAttributes = [])
    {
        $result = $this->call('customerCustomerInfo', [$id, $onlyAttributes]);

        if ($isIncludeAddresses) {
            $result->addresses = $this->getCustomerAddressData($id);
        }

        return $result;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerAddressData($customerId)
    {
        return $this->call('customerAddressList', $customerId);
    }

    public function saveCustomerData()
    {
        return [];
    }
}
