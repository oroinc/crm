<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class CartConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.cart.label';
    const JOB_VALIDATE_IMPORT = 'mage_cart_import_validation';
    const JOB_IMPORT          = 'mage_cart_import';

    const ACTION_CART_LIST    = 'salesQuoteList';

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $filters = [];

        $result = $this->getQuoteList($filters);
    }

    /**
     * @param array $filters
     * @return mixed
     */
    public function getQuoteList($filters = [])
    {
        return $this->call(self::ACTION_CART_LIST, $filters);
    }
}
