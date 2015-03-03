<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Connector;

use OroCRM\Bundle\MagentoBundle\Provider\CartConnector;
use OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;

class InitialCartConnector extends AbstractInitialConnector implements ExtensionAwareInterface
{
    const TYPE = 'cart_initial';

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
        return CartConnector::IMPORT_JOB_NAME;
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
