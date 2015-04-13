<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class StoresSoapIterator extends AbstractLoadeableSoapIterator
{
    const ADMIN_STORE_ID = 0;
    const ADMIN_WEBSITE_ID = 0;
    const ALL_WEBSITES = -1;

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
                'website_id' => self::ADMIN_WEBSITE_ID,
                'code'       => 'admin',
                'name'       => 'Admin',
                'store_id'   => self::ADMIN_STORE_ID
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
