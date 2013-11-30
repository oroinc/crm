<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class StoreConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Store';
    const ACTION_STORE_LIST   = 'storeList';

    /**
     * @param null|int $storeId
     * @return array
     */
    public function getStores($storeId = null)
    {
        $result = $this->call(self::ACTION_STORE_LIST);

        $stores = [];
        foreach ($result as $item) {
            $stores[$item->store_id]       = (array)$item;
            $stores[$item->store_id]['id'] = $item->store_id;
        }

        // add default/admin store
        $stores[0] = [
            'website_id' => 0,
            'code'       => 'admin',
            'name'       => 'Admin',
        ];

        return $stores;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.store.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::ENTITY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName($isValidationOnly = false)
    {
        throw new \Exception("Not applicable yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
    }
}
