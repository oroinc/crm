<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

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
