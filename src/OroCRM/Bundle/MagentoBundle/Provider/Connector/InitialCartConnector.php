<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

class InitialCartConnector extends AbstractInitialConnector
{
    const TYPE = 'cart_initial';
    const IMPORT_JOB_NAME = 'mage_cart_initial_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.cart.initial.label';
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
        return $this->transport->getCarts();
    }
}
