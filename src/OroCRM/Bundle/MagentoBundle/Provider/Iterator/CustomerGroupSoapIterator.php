<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerGroupSoapIterator extends AbstractLoadeableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        $result = $this->transport->call(SoapTransport::ACTION_GROUP_LIST);

        return $this->processCollectionResponse($result);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return (array)parent::current();
    }
}
