<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

class InitialOrderConnector extends AbstractMagentoConnector implements InitialConnectorInterface
{
    const TYPE = 'order_initial';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.order.initial.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return OrderConnector::IMPORT_JOB_NAME;
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

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
