<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\OrderDependencyManager;

class OrderInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $result = $this->transport->getOrderInfo($originId);

        OrderDependencyManager::addDependencyData($result, $this->transport);

        return ConverterUtils::objectToArray($result);
    }
}
