<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class CartConnector extends AbstractMagentoConnector implements ExtensionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.cart.label';
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
        return 'mage_cart_import';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        return $this->transport->getCarts();
    }
}
