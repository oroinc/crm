<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

class CustomerInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    protected function loadEntityInfo($originId)
    {
        $result = $this->transport->getCustomerInfo($originId);
        $result['addresses'] = $this->transport->getCustomerAddresses($originId);

        return $result;
    }
}
