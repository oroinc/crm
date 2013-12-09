<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class OrderConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const ENTITY_NAME     = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order';
    const CONNECTOR_LABEL = 'orocrm.magento.connector.order.label';

    const JOB_VALIDATE_IMPORT = 'mage_order_import_validation';
    const JOB_IMPORT          = 'mage_order_import';

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateInterval */
    protected $syncRange;

    /** @var array */
    protected $customerIdsBuffer = [];

    /** @var int */
    protected $batchSize;

    /** @var array dependencies data: customer groups, stores, websites */
    protected $dependencies = [];

    /** @var StoreConnector */
    protected $storeConnector;

    /**
     * @param ContextRegistry $contextRegistry
     * @param StoreConnector  $storeConnector
     * @param LoggerStrategy  $logger
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        StoreConnector $storeConnector
    ) {
        parent::__construct($contextRegistry, $logger);
        $this->storeConnector = $storeConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        $this->preLoadDependencies();

        $result = $this->findOrdersToImport();
        // no more data to look for
        if (is_null($result)) {
            return null;
        }

        // keep going till endDate >= NOW
        if (!empty($this->orderIdsBuffer)) {
            $orderId = array_shift($this->orderIdsBuffer);

            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->logger->info(sprintf('[%s] loading order ID: %d', $now->format('d-m-Y H:i:s'), $orderId));

            $data = $this->loadOrderById($orderId, true);
        } else {
            // empty record, nothing found but keep going
            $data = false;
        }

        return $data;
    }

    protected function findOrdersToImport()
    {

    }

    protected function loadOrderById($id)
    {

    }

    /**
     * Pre-load dependencies
     */
    protected function preLoadDependencies()
    {
        if (!empty($this->dependencies)) {
            return;
        }

        $this->dependencies[self::ALIAS_STORES]   = $this->storeConnector->getStores();
        $this->dependencies[self::ALIAS_WEBSITES] = $this->storeConnector->getWebsites(
            $this->dependencies[self::ALIAS_STORES]
        );
    }
}
