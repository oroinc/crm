<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;

class WebsiteConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    const TYPE = 'website_dictionary';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getWebsites();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.website.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_website_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
