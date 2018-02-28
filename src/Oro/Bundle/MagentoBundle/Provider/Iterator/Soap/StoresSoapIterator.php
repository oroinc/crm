<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class StoresSoapIterator extends AbstractLoadeableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        $data   = [];
        $result = $this->transport->call(SoapTransport::ACTION_STORE_LIST);
        $result = $this->processCollectionResponse($result);

        if (!empty($result) && is_array($result)) {
            $adminStoreData = [
                'website_id' => Website::ADMIN_WEBSITE_ID,
                'code'       => 'admin',
                'name'       => 'Admin',
                'store_id'   => Store::ADMIN_STORE_ID
            ];
            array_unshift($result, $adminStoreData);

            foreach ($result as $item) {
                $item                    = (array)$item;
                $data[$item['store_id']] = array_intersect_key($item, array_flip(array_keys($adminStoreData)));
            }
        }

        return $data;
    }
}
