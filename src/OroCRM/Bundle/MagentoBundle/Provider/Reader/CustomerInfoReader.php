<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CustomerDependencyManager;

class CustomerInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $result = $this->transport->getCustomerInfo($originId);
        $result->addresses = $this->transport->getCustomerAddresses($originId);
        foreach ($result->addresses as $key => $val) {
            $result->addresses[$key] = (array)$val;
        }

        CustomerDependencyManager::addDependencyData($result, $this->transport);

        return ConverterUtils::objectToArray($result);
    }
}
