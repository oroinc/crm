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
        $result = $this->processCollectionResponse($result);

        $data = [];
        foreach ($result as $group) {
            $group->id        = $group->customer_group_id;
            $group->name      = $group->customer_group_code;
            $data[$group->id] = (array)$group;
        }

        return $data;
    }
}
