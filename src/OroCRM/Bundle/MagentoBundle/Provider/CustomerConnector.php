<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class CustomerConnector extends AbstractConnector implements CustomerConnectorInterface
{
    const DEFAULT_SYNC_RANGE  = '1 month';
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer';
    const JOB_VALIDATE_IMPORT = 'mage_customer_import_validation';
    const JOB_IMPORT          = 'mage_customer_import';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.customer.label';
    const TYPE_KEY            = 'customer';

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
        StoreConnector $storeConnector,
        LoggerStrategy $logger
    ) {
        parent::__construct($contextRegistry, $logger);
        $this->storeConnector = $storeConnector;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $settings = $this->transportSettings->getSettingsBag()->all();

        $startSyncDateKey = 'start_sync_date';
        if (empty($settings[$startSyncDateKey])) {
            throw new \LogicException('Start sync date can\'t be empty');
        }

        if (!($settings[$startSyncDateKey] instanceof \DateTime)) {
            $settings[$startSyncDateKey] = new \DateTime($settings[$startSyncDateKey]);
        }

        $startSyncFrom = $settings[$startSyncDateKey];
        /** @var Channel $channel */
        $channel = $context->getOption('channel');
        $status  = $channel->getStatusesForConnector(self::TYPE_KEY, Status::STATUS_COMPLETED)->first();
        if (false !== $status) {
            /** @var Status $status */
            $startSyncFrom = $status->getDate();
        }

        $this->lastSyncDate = $startSyncFrom;

        if (empty($settings['sync_range'])) {
            $settings['sync_range'] = self::DEFAULT_SYNC_RANGE;
        }
        if ($settings['sync_range'] instanceof \DateInterval) {
            $this->syncRange = $settings['sync_range'];
        } else {
            $this->syncRange = \DateInterval::createFromDateString($settings['sync_range']);
        }

        if (!empty($settings['batch_size'])) {
            $this->batchSize = $settings['batch_size'];
        }

        // init helper connector
        $this->storeConnector->setStepExecution($this->getStepExecution());
    }

    /**
     * @param int       $websiteId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string    $format
     *
     * @return array
     */
    protected function getBatchFilter($websiteId, \DateTime $startDate, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
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
                    'key'   => 'website_id',
                    'value' => ['key' => 'eq', 'value' => $websiteId]
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        $this->preLoadDependencies();

        $result = $this->findCustomersToImport();
        // no more data to look for
        if (is_null($result)) {
            return null;
        }

        // keep going till endDate >= NOW
        if (!empty($this->customerIdsBuffer)) {
            $customerId = array_shift($this->customerIdsBuffer);

            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->logger->info(sprintf('[%s] loading customer ID: %d', $now->format('d-m-Y H:i:s'), $customerId));

            $data = $this->getCustomerData($customerId, true);
        } else {
            // empty record, nothing found but keep going
            $data = false;
        }

        return $data;
    }

    /**
     * Fill customer ids buffer with found customers
     * in specific date range
     *
     * @return bool|null
     */
    protected function findCustomersToImport()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!empty($this->customerIdsBuffer)) {
            return false;
        }

        $startDate = $this->lastSyncDate;
        $endDate   = clone $this->lastSyncDate;
        $endDate   = $endDate->add($this->syncRange);

        if ($startDate >= $now) {
            return null;
        }

        $this->logger->info(
            sprintf(
                '[%s] Looking for entities from %s to %s ... ',
                $now->format('d-m-Y H:i:s'),
                $startDate->format('d-m-Y H:i:s'),
                $endDate->format('d-m-Y H:i:s')
            )
        );

        $filters                 = [
            $this->getBatchFilter(
                $this->transportSettings->getWebsiteId(),
                $startDate,
                $endDate
            )
        ];
        $this->customerIdsBuffer = $this->getCustomersList($filters, $this->batchSize, true);

        $this->logger->info(sprintf('found %d customers', count($this->customerIdsBuffer)));
        $this->lastSyncDate = $endDate;

        // no more data to look for
        if (empty($this->customerIdsBuffer) && $endDate >= $now) {
            $result = null;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Pre-load dependencies
     */
    protected function preLoadDependencies()
    {
        if (!empty($this->dependencies)) {
            return;
        }

        foreach ([self::ALIAS_GROUPS, self::ALIAS_STORES, self::ALIAS_WEBSITES] as $item) {
            switch ($item) {
                case self::ALIAS_GROUPS:
                    $this->dependencies[self::ALIAS_GROUPS] = $this->getCustomerGroups();
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
    public function getCustomersList($filters = [], $batchSize = null, $idOnly = false)
    {
        $result = $this->call(CustomerConnectorInterface::ACTION_CUSTOMER_LIST, $filters);

        if ($idOnly) {
            $result = array_map(
                function ($item) {
                    return is_object($item) ? $item->customer_id : $item['customer_id'];
                },
                $result
            );
        }

        if ((int)$batchSize > 0) {
            $result = array_slice($result, 0, $batchSize);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerData($id, $isAddressesIncluded = false, $onlyAttributes = null)
    {
        $result = $this->call(CustomerConnectorInterface::ACTION_CUSTOMER_INFO, [$id, $onlyAttributes]);

        if ($isAddressesIncluded) {
            $result->addresses = $this->getCustomerAddressData($id);
            foreach ($result->addresses as $key => $val) {
                $result->addresses[$key] = (array)$val;
            }
        }

        $result->group = $this->dependencies[self::ALIAS_GROUPS][$result->group_id];
        if ($result->group) {
            $result->group['originId'] = $result->group['customer_group_id'];
        }
        $result->store   = $this->dependencies[self::ALIAS_STORES][$result->store_id];
        $result->website = $this->dependencies[self::ALIAS_WEBSITES][$result->website_id];

        return (array)$result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressData($customerId)
    {
        return $this->call(CustomerConnectorInterface::ACTION_ADDRESS_LIST, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups($groupId = null)
    {
        if (!empty($this->dependencies[self::ALIAS_GROUPS])) {
            $groups = $this->dependencies[self::ALIAS_GROUPS];
        } else {
            $result = $this->call(CustomerConnectorInterface::ACTION_GROUP_LIST);

            $groups = [];
            foreach ($result as $item) {
                $item->id                         = $item->customer_group_id;
                $item->name                       = $item->customer_group_code;
                $groups[$item->customer_group_id] = (array)$item;
            }
        }

        if (!is_null($groupId) && isset($groups[$groupId])) {
            $result = [$groupId => $groups[$groupId]];
        } else {
            $result = $groups;
        }

        return $result;
    }
}
