<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class ProductConnector extends AbstractConnector
{
    const ENTITY_NAME     = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Product';
    const CONNECTOR_LABEL = 'orocrm.magento.connector.product.label';

    const JOB_VALIDATE_IMPORT = null;
    const JOB_IMPORT          = null;

    /** @var LoggerStrategy */
    protected $logger;

    public function __construct(LoggerStrategy $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        // TODO: Implement read() method.
    }
}
