<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class OrderInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $result = $this->transport->getOrderInfo($originId);

        return ConverterUtils::objectToArray($result);
    }
}
