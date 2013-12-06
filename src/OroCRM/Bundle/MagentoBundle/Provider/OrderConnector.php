<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Guesser\TransportGuesser;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class OrderConnector extends AbstractConnector
{
    const ENTITY_NAME = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order';
    const CONNECTOR_LABEL = 'orocrm.magento.connector.order.label';

    const JOB_VALIDATE_IMPORT = 'mage_order_import_validation';
    const JOB_IMPORT          = 'mage_order_import';

    /** @var LoggerStrategy */
    protected $logger;

    public function __construct(
        ContextRegistry $contextRegistry,
        TransportGuesser $transportGuesser,
        LoggerStrategy $logger
    ) {
        parent::__construct($contextRegistry, $transportGuesser);
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        // TODO: Implement doRead() method.
    }
}
