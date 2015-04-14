<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;

class StoreConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const TYPE = 'store_dictionary';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getStores();
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
    public function getImportJobName()
    {
        return 'mage_store_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
