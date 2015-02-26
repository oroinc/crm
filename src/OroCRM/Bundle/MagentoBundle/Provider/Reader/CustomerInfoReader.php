<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CustomerDependencyManager;

class CustomerInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        /** @var Customer $customer */
        $customer = $this->getData();
        $originId = $customer->getOriginId();

        if (empty($originId) || !empty($this->loaded[$originId])) {
            return null;
        }

        $result = $this->transport->getCustomerInfo($customer);
        $result->addresses = $this->transport->getCustomerAddresses($customer);
        foreach ($result->addresses as $key => $val) {
            $result->addresses[$key] = (array)$val;
        }

        $this->loaded[$originId] = true;

        CustomerDependencyManager::addDependencyData($result, $this->transport->getDependencies());

        return ConverterUtils::objectToArray($result);
    }
}
