<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class OrderInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $this->logger->info(sprintf('Loading Order info by incrementId: %s', $originId));

        $result = $this->transport->getOrderInfo($originId);

        return ConverterUtils::objectToArray($result);
    }
}
