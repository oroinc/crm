<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class CustomerConnector extends AbstractConnector implements CustomerConnectorInterface
{
    const DEFAULT_SYNC_RANGE = '1 week';
    /**
     * {@inheritdoc}
     */
    public function sync($oneWay = self::SYNC_DIRECTION_PULL, $params = [])
    {
        $channelSettings = $this->channel->getSettings();

        $lastSyncDate = isset($channelSettings['last_sync_date']) ? $channelSettings['last_sync_date'] : null;
        if (empty($lastSyncDate)) {
            throw new InvalidConfigurationException('Last (starting) sync date can\'t be empty');
        }
        $lastSyncDate = new \DateTime($lastSyncDate);

        $syncRange = isset($channelSettings['sync_range']) ? $channelSettings['sync_range'] : null;
        if (empty($syncRange)) {
            throw new InvalidConfigurationException('Sync range can\'t be empty');
        }
        $syncRange = \DateInterval::createFromDateString($syncRange);

        $filters = function ($startDate, $endDate) {
            return [
                ['complex_filter' => [
                        [
                            'key'   => 'created_at',
                            'value' => ['key'   => 'gteq', 'value' => $startDate],
                        ],
                        [
                            'key'   => 'created_at',
                            'value' => ['key'   => 'lt', 'value' => $endDate],
                        ],
                    ]
                ]
            ];
        };

        $startDate = $lastSyncDate;
        $endDate = $lastSyncDate->add($syncRange);
        do {
            $hasData = true;

            // TODO: implement bi-directional sync

            $batchData = $this->getCustomersList($filters($startDate, $endDate));

            if (!empty($batchData)) {
                $this->processSyncBatch($batchData);
            } else {
                $hasData = false;
            }

            // move date range, from end to start, allow new customers to be imported first
            $endDate = $startDate;

        } while ($hasData);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomersList($filters = [])
    {
        return $this->call('customerCustomerList', $filters);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerData($id, $isAddressesIncluded = false, $isGroupsIncluded = false, $onlyAttributes = [])
    {
        $result = $this->call('customerCustomerInfo', [$id, $onlyAttributes]);

        if ($isAddressesIncluded) {
            $result->addresses = $this->getCustomerAddressData($id);
        }

        if ($isGroupsIncluded) {
            $result->groups = $this->getCustomerGroups($result->group_id);
            $result->group_name = $result->groups[$result->group_id];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressData($customerId)
    {
        return $this->call('customerAddressList', $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups($groupId = null)
    {
        $result = $this->call('customerGroupList');

        $groups = [];
        foreach ($result as $item) {
            $groups[$item->customer_group_id] = $item->customer_group_code;
        }

        if (!is_null($groupId) && isset($groups[$groupId])) {
            $result = [$groupId => $groups[$groupId]];
        } else {
            $result = $groups;
        }

        return  $result;
    }

    /**
     * @return mixed
     */
    public function getStoresData()
    {
        return $this->call('storeList');
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
}
