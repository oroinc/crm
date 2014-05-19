<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class CustomerConnector extends AbstractMagentoConnector implements TwoWaySyncConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.customer.label';
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
        return 'mage_customer_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getExportJobName()
    {
        return 'mage_customer_reverse_sync';
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
    protected function supportsForceSync()
    {
        return true;
    }
}
