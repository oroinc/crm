<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class CustomerConnector extends AbstractMagentoConnector
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
}
