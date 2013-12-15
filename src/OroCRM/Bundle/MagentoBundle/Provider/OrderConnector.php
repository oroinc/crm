<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;

class OrderConnector extends AbstractApiBasedConnector implements MagentoConnectorInterface
{
    const ENTITY_NAME     = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order';
    const CONNECTOR_LABEL = 'orocrm.magento.connector.order.label';

    const JOB_VALIDATE_IMPORT = 'mage_order_import_validation';
    const JOB_IMPORT          = 'mage_order_import';

    /** @var CustomerConnector */
    protected $customerConnector;

    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        EntityManager $em,
        StoreConnector $storeConnector,
        CustomerConnector $customerConnector
    ) {
        parent::__construct($contextRegistry, $logger, $em, $storeConnector);
        $this->customerConnector = $customerConnector;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // init helper connectors
        $this->customerConnector->setStepExecution($this->getStepExecution());
    }

    /**
     * {@inheritdoc}
     */
    protected function getBatchFilter($websiteId, \DateTime $startDate, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
        $store = array_filter(
            $this->dependencies[self::ALIAS_STORES],
            function ($store) use ($websiteId) {
                return $store['website_id'] == $websiteId;
            }
        );
        $store = reset($store);

        if ($store === false) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        return [
            'complex_filter' => [
                [
                    'key'   => 'updated_at',
                    'value' => [
                        'key'   => 'from',
                        'value' => $startDate->format($format),
                    ],
                ],
                [
                    'key'   => 'updated_at',
                    'value' => [
                        'key'   => 'to',
                        'value' => $endDate->format($format),
                    ],
                ],
                [
                    'key'   => 'store_id',
                    'value' => ['key' => 'eq', 'value' => $store['store_id']]
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getList($filters = [], $limit = null, $idsOnly = true)
    {
        $result = $this->call(MagentoConnectorInterface::ACTION_ORDER_LIST, $filters);

        if ($idsOnly) {
            $result = array_map(
                function ($item) {
                    return is_object($item) ? $item->increment_id : $item['increment_id'];
                },
                $result
            );
        }

        if ((int)$limit > 0) {
            $result = array_slice($result, 0, $limit);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($id, $dependenciesInclude = false, $onlyAttributes = null)
    {
        $result = $this->call(MagentoConnectorInterface::ACTION_ORDER_LIST, [$id, $onlyAttributes]);

        return [];
    }


    /**
     * @param int $id
     *
     * @return array
     */
    protected function getStoreDataById($id)
    {
        $store            = $this->dependencies[self::ALIAS_STORES][$id];
        $store['website'] = $this->dependencies[self::ALIAS_WEBSITES][$store['website_id']];

        return $store;
    }

    /**
     * @param $id
     *
     * @return array
     */
    protected function getCustomerGroupDataById($id)
    {
        return $this->dependencies[self::ALIAS_GROUPS][$id];
    }

    /**
     * {@inheritdoc}
     */
    protected function loadDependencies()
    {
        foreach ([self::ALIAS_GROUPS, self::ALIAS_STORES, self::ALIAS_WEBSITES] as $item) {
            switch ($item) {
                case self::ALIAS_GROUPS:
                    $this->dependencies[self::ALIAS_GROUPS] = $this->customerConnector->getCustomerGroups();
                    break;
                case self::ALIAS_STORES:
                    $this->dependencies[self::ALIAS_STORES] = $this->storeConnector->getStores();
                    break;
                case self::ALIAS_WEBSITES:
                    $this->dependencies[self::ALIAS_WEBSITES] = $this->storeConnector->getWebsites(
                        $this->dependencies[self::ALIAS_STORES]
                    );
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'order';
    }
}
