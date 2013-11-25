<?php

namespace OroCRM\Bundle\DemoIntegrationBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorTypeInterface;

class MagentoProductConnector implements ConnectorTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.demo_integration.magento_product.connector.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        // TODO: Implement getImportEntityFQCN() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName($isValidationOnly = false)
    {
        // TODO: Implement getImportJobName() method.
    }
}
