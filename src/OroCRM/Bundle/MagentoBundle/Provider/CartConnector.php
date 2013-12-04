<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class CartConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.cart.label';
    const JOB_VALIDATE_IMPORT = 'mage_cart_import_validation';
    const JOB_IMPORT          = 'mage_cart_import';


    /**
     * {@inheritdoc}
     */
    public function read()
    {
        // TODO: Implement read() method.
    }
}
