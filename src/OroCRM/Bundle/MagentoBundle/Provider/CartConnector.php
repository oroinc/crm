<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class CartConnector extends AbstractApiBasedConnector implements MagentoConnectorInterface, ExtensionAwareInterface
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.cart.label';
    const JOB_VALIDATE_IMPORT = 'mage_cart_import_validation';
    const JOB_IMPORT          = 'mage_cart_import';

    const PAGE_SIZE = 10;

    /** @var int */
    protected $currentPage = 1;

    /** @var array dependencies data: customer groups, stores */
    protected $dependencies = [];

    /** @var CustomerConnector */
    protected $customerConnector;

    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        CustomerConnector $customerConnector,
        StoreConnector $storeConnector,
        EntityManager $em
    ) {
        parent::__construct($contextRegistry, $logger, $em, $storeConnector);

        $this->customerConnector = $customerConnector;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBatchFilter($websiteId, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
        $stores = $this->getStoresByWebsiteId($websiteId);

        return [
            'complex_filter' => [
                [
                    'key'   => 'store_id',
                    'value' => ['key' => 'in', 'value' => implode(',', $stores)]
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getList($filters = [], $limit = null, $idsOnly = true)
    {
        $quoteQueue = $this->getQuoteList(
            $filters,
            ['page' => $this->currentPage, 'pageSize' => self::PAGE_SIZE]
        );

        return $quoteQueue;
    }

    /**
     * Load entities ids list
     *
     * @return bool|null
     */
    protected function findEntitiesToProcess()
    {
        if (!empty($this->entitiesIdsBuffer)) {
            return false;
        }

        $this->logger->info(sprintf('Looking for entities at %d page ... ', $this->currentPage));

        $filters = $this->getBatchFilter(
            $this->transportSettings->get('website_id'),
            $this->lastSyncDate
        );

        $this->entitiesIdsBuffer = $this->getList($filters, $this->batchSize, true);
        $this->currentPage++;

        $this->logger->info(sprintf('%d records', count($this->entitiesIdsBuffer), $this->currentPage));

        return empty($this->entitiesIdsBuffer) ? null : $this->entitiesIdsBuffer;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($id, $dependenciesInclude = false, $onlyAttributes = null)
    {
        $result = $id;

        // fill related entities data
        $store                      = $this->getStoreDataById($result->store_id);
        $result->store_code         = $store['code'];
        $result->store_name         = $store['name'];
        $result->store_website_id   = $store['website']['id'];
        $result->store_website_code = $store['website']['code'];
        $result->store_website_name = $store['website']['name'];

        $customer_group              = $this->getCustomerGroupDataById($result->customer_group_id);
        $result->customer_group_code = $customer_group['customer_group_code'];
        $result->customer_group_name = $customer_group['name'];

        return ConverterUtils::objectToArray($result);
    }

    /**
     * @param array $filters
     * @param array $limits
     *
     * @return mixed
     */
    public function getQuoteList($filters = [], $limits = [])
    {
        if (empty($limits)) {
            $limits = [
                'page'     => 1,
                'pageSize' => 15,
            ];
        }

        return $this->call(self::ACTION_CART_LIST, [$filters, $limits]);
    }

    /**
     * @param int $quoteId
     *
     * @return mixed
     */
    public function getQuoteInfo($quoteId)
    {
        return $this->call(self::ACTION_CART_INFO, $quoteId);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // init helper connectors
        $this->storeConnector->setStepExecution($this->getStepExecution());
        $this->customerConnector->setStepExecution($this->getStepExecution());

        // restore empty state
        $this->currentPage = 1;
        $this->dependencies = [];
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
     * Load stores and customer groups data
     */
    public function loadDependencies()
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
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'entity_id';
    }
}
