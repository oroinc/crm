<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

class InitialCustomerConnector extends AbstractMagentoConnector implements InitialConnectorInterface
{
    const TYPE = 'customer_initial';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.customer.initial.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return CustomerConnector::IMPORT_JOB_NAME;
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

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
