<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;

class CartConnector extends AbstractMagentoConnector implements ExtensionAwareInterface
{
    const IMPORT_JOB_NAME = 'mage_cart_import';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.cart.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::CART_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'cart';
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
