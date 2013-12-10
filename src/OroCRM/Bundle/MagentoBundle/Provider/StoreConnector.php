<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

/***
 * Class StoreConnector
 *
 * @package OroCRM\Bundle\MagentoBundle\Provider
 *
 * Just a fake connector for internal needs
 * It's not registered as connector service
 */
class StoreConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const ENTITY_NAME = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Store';

    const STORE_TYPE   = 'OroCRM\Bundle\MagentoBundle\Entity\Store';
    const WEBSITE_TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\Website';

    const WEBSITE_CODE_SEPARATOR = ' / ';
    const WEBSITE_NAME_SEPARATOR = ', ';

    /**
     * @return array
     */
    public function getStores()
    {
        $result = $this->call(MagentoConnectorInterface::ACTION_STORE_LIST);

        $stores = $websites = [];
        foreach ($result as $item) {
            $stores[$item->store_id]       = (array)$item;
            $stores[$item->store_id]['id'] = $item->store_id;
        }

        // add default/admin store
        $stores[0] = [
            'website_id' => 0,
            'code'       => 'admin',
            'name'       => 'Admin',
            'origin_id'  => 0
        ];

        return $stores;
    }

    /**
     * @param array $stores
     *
     * @return array
     */
    public function getWebsites(array $stores)
    {
        $websites = [];
        foreach ($stores as $store) {
            $websites[$store['website_id']]['name'][] = $store['name'];
            $websites[$store['website_id']]['code'][] = $store['code'];
        }

        foreach ($websites as $websiteId => $websiteItem) {
            $websites[$websiteId]['name'] = implode(self::WEBSITE_NAME_SEPARATOR, $websiteItem['name']);
            $websites[$websiteId]['code'] = implode(self::WEBSITE_CODE_SEPARATOR, $websiteItem['code']);
            $websites[$websiteId]['id']   = $websiteId;
        }

        return $websites;
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
    public function doRead()
    {
        /**
         * @TODO FIXME review implementation
         */
    }
}
