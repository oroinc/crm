<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;

class InitialCartConnector extends AbstractMagentoConnector implements
    ExtensionAwareInterface,
    InitialConnectorInterface
{
    const TYPE = 'cart_initial';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.cart.initial.label';
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

    /**
     * {@inheritdoc}
     */
    public function supportsForceSync()
    {
        return true;
    }
}
