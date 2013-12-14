<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class ProductConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const ENTITY_NAME     = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Product';
    const CONNECTOR_LABEL = 'orocrm.magento.connector.product.label';

    const JOB_VALIDATE_IMPORT = null;
    const JOB_IMPORT          = null;

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        // TODO: Implement doRead() method.
    }
}
