<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

class InitialCustomerConnector extends AbstractInitialConnector
{
    const TYPE = 'customer_initial';
    const IMPORT_JOB_NAME = 'mage_customer_initial_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.customer.initial.label';
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
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getCustomers();
    }
}
