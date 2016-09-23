<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

class CustomerInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $this->logger->info(sprintf('Loading Customer info by id: %s', $originId));

        $result = $this->transport->getCustomerInfo($originId);
        if (!array_key_exists('addresses', $result)) {
            $result['addresses'] = $this->transport->getCustomerAddresses($originId);
        }

        return $result;
    }
}
