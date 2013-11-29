<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class CustomerConnector extends AbstractConnector implements CustomerConnectorInterface
{
    const DEFAULT_SYNC_RANGE = '2 year'; // '1 week';
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer';
    const JOB_VALIDATE_IMPORT = 'mage_customer_import_validation';
    const JOB_IMPORT          = 'mage_customer_import';

    const ALIAS_GROUPS   = 'groups';
    const ALIAS_STORES   = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS  = 'regions';

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateInterval */
    protected $syncRange;

    /** @var array */
    protected $customerIdsBuffer = [];

    /** @var int */
    protected $batchSize;

    /** @var array dependencies data: customer groups, stores, websites, regions? */
    protected $dependencies = [];

    public function configure(TransportInterface $realTransport, Transport $transportSettings)
    {
        $settings = $transportSettings->getSettingsBag()->all();

        if (empty($settings['last_sync_date'])) {
            throw new \LogicException('Last sync date can\'t be empty');
        } elseif ($settings['last_sync_date'] instanceof \DateTime) {
            $this->lastSyncDate = $settings['last_sync_date'];
        } else {
            $this->lastSyncDate = new \DateTime($settings['last_sync_date']);
        }

        if (empty($settings['sync_range'])) {
            $settings['sync_range'] = self::DEFAULT_SYNC_RANGE;
        } elseif ($settings['sync_range'] instanceof \DateInterval) {
            $this->syncRange = $settings['sync_range'];
        }

        $this->syncRange = \DateInterval::createFromDateString($settings['sync_range']);

        if (!empty($settings['batch_size'])) {
            $this->batchSize = $settings['batch_size'];
        }

        parent::configure($realTransport, $transportSettings);
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
    public function read()
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

            // TODO: log
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            echo $now->format('d-m-Y H:i:s') . " loading customer $customerId\n";

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

        // TODO: remove / log
        echo sprintf(
            '[%s] Looking for entities from %s to %s ... ',
            $now->format('d-m-Y H:i:s'),
            $startDate->format('d-m-Y'),
            $endDate->format('d-m-Y')
        );

        $filters   = [];
        $filters[] = $this->getBatchFilter($this->transportSettings->getWebsiteId(), $startDate, $endDate);
        $this->customerIdsBuffer = $this->getCustomersList($filters, $this->batchSize, true);

        // TODO: remove / log
        echo sprintf('found %d customers', count($this->customerIdsBuffer)) . "\n";

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

        foreach ([self::ALIAS_GROUPS, self::ALIAS_STORES, self::ALIAS_WEBSITES, self::ALIAS_REGIONS] as $item) {
            switch ($item) {
                case self::ALIAS_GROUPS:
                    $this->dependencies[self::ALIAS_GROUPS] = $this->getCustomerGroups();
                    break;
                case self::ALIAS_STORES:
                    $this->dependencies[self::ALIAS_STORES] = $this->getStores();
                    break;
                case self::ALIAS_WEBSITES:
                    // TODO: refactor this code to present websites data in some way
                    $websites = [];
                    foreach ($this->dependencies[self::ALIAS_STORES] as $store) {
                        $websites[$store['website_id']]['name'][] = $store['name'];
                        $websites[$store['website_id']]['code'][] = $store['code'];
                    }

                    foreach ($websites as $websiteId => $websiteItem) {
                        $websites[$websiteId]['name'] = implode(', ', $websiteItem['name']);
                        $websites[$websiteId]['code'] = implode(' / ', $websiteItem['code']);
                        $websites[$websiteId]['id']   = $websiteId;
                    }

                    $this->dependencies[self::ALIAS_WEBSITES] = $websites;
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
    public function getCustomerData(
        $id,
        $isAddressesIncluded = false,
        $onlyAttributes = null
    ) {
        $result = $this->call(CustomerConnectorInterface::ACTION_CUSTOMER_INFO, [$id, $onlyAttributes]);

        if ($isAddressesIncluded) {
            $result->addresses = $this->getCustomerAddressData($id);
            foreach ($result->addresses as $key => $val) {
                $result->addresses[$key] = (array)$val;
            }
        }

        $result->group   = $this->dependencies[self::ALIAS_GROUPS][$result->group_id];
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

    /**
     * TODO: consider move this to some sort of store/website connecor and inject it here
     *
     * {@inheritdoc}
     */
    public function getStores($storeId = null)
    {
        if (!empty($this->dependencies[self::ALIAS_STORES])) {
            $stores = $this->dependencies[self::ALIAS_STORES];
        } else {
            $result = $this->call(CustomerConnectorInterface::ACTION_STORE_LIST);

            $stores = [];
            foreach ($result as $item) {
                $stores[$item->store_id]       = (array)$item;
                $stores[$item->store_id]['id'] = $item->store_id;
            }

            // add default/admin store
            $stores[0] = [
                'website_id' => 0,
                'code'       => 'admin',
                'name'       => 'Admin',
            ];
        }

        if (!is_null($storeId) && isset($stores[$storeId])) {
            return [$storeId => $stores[$storeId]];
        } else {
            return $stores;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveCustomerData()
    {
        // TODO: implement create/update customer data
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function saveCustomerAddress()
    {
        // TODO: implement create/update customer address
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.customer.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::ENTITY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName($isValidationOnly = false)
    {
        if ($isValidationOnly) {
            return self::JOB_VALIDATE_IMPORT;
        }

        return self::JOB_IMPORT;
    }
}
