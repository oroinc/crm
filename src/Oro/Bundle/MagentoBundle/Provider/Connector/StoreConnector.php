<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;

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
        return 'oro.magento.connector.store.label';
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
