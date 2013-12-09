<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class CartConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.cart.label';
    const JOB_VALIDATE_IMPORT = 'mage_cart_import_validation';
    const JOB_IMPORT          = 'mage_cart_import';

    const ACTION_CART_LIST    = 'salesQuoteList';
    const PAGE_SIZE           = 10;

    const ALIAS_GROUPS        = 'groups';
    const ALIAS_STORES        = 'stores';
    const ALIAS_WEBSITES      = 'websites';

    /** @var int */
    protected $currentPage = 1;

    /** @var array */
    protected $quoteQueue = [];

    /** @var array dependencies data: customer groups, stores */
    protected $dependencies = [];

    /** @var CustomerConnector */
    protected $customerConnector;

    /** @var StoreConnector */
    protected $storeConnector;

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        $this->preLoadDependencies();
        $result = $this->getNextItem();

        if (empty($result)) {
            return null; // no more data
        }

        // fill related entities data
        $store = $this->getStoreDataById($result->store_id);
        $result->store_code = $store['code'];
        $result->store_name = $store['name'];
        $result->store_website_id = $store['website']['id'];
        $result->store_website_code = $store['website']['code'];
        $result->store_website_name = $store['website']['name'];

        $customer_group = $this->getCustomerGroupDataById($result->customer_group_id);
        $result->customer_group_code = $customer_group['customer_group_code'];
        $result->customer_group_name = $customer_group['name'];

        $result = ConverterUtils::objectToArray($result);
        $this->currentPage++;

        return (array) $result;
    }

    /**
     * Return next quote data from loaded queue or remote API
     *
     * @return array
     */
    protected function getNextItem()
    {
        $filters = [];

        if (empty($this->quoteQueue)) {
            $this->logger->info(sprintf('Looking for entities at %d page ... ', $this->currentPage));

            $this->quoteQueue = $this->getQuoteList(
                $filters,
                ['page' => $this->currentPage, 'pageSize' => self::PAGE_SIZE]
            );

            $this->logger->info(sprintf('%d records', count($this->quoteQueue), $this->currentPage));
        }

        return array_shift($this->quoteQueue);
    }

    /**
     * @param array $filters
     * @param array $limits
     * @return mixed
     */
    public function getQuoteList($filters = [], $limits = [])
    {
        if (empty($limits)) {
            $limits = [
                'page' => 1,
                'pageSize' => 15,
            ];
        }

        return $this->call(self::ACTION_CART_LIST, [$filters, $limits]);
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
    }

    /**
     * @param int $id
     *
     * @return array
     */
    protected function getStoreDataById($id)
    {
        $store = $this->dependencies[self::ALIAS_STORES][$id];
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
    public function preLoadDependencies()
    {
        if (!empty($this->dependencies)) {
            return;
        }

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
     * @param CustomerConnector $customerConnector
     */
    public function setCustomerConnector(CustomerConnector $customerConnector)
    {
        $this->customerConnector = $customerConnector;
    }

    /**
     * @param StoreConnector $storeConnector
     */
    public function setStoreConnector(StoreConnector $storeConnector)
    {
        $this->storeConnector = $storeConnector;
    }
}
