<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class StoresSoapIterator extends AbstractLoadeableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        $data   = [];
        $result = $this->transport->call(SOAPTransport::ACTION_STORE_LIST);

        if (!empty($result) && is_array($result)) {
            array_unshift(
                $result,
                [
                    'website_id' => 0,
                    'code'       => 'admin',
                    'name'       => 'Admin',
                    'store_id'   => 0
                ]
            );

            foreach ($result as $item) {
                $item                    = (array)$item;
                $data[$item['store_id']] = $item;
            }
        }

        return $data;
    }
}
