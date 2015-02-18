<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

class InitialOrderConnector extends AbstractInitialConnector
{
    const TYPE = 'order_initial';
    const IMPORT_JOB_NAME = 'mage_order_initial_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.order.initial.label';
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
        return $this->transport->getOrders();
    }
}
