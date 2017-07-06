<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class CustomerConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    const IMPORT_JOB_NAME = 'mage_customer_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.customer.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::CUSTOMER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return 'magento_customer_export';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getCustomers();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
