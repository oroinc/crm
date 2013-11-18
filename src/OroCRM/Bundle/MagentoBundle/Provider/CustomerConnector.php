<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class CustomerConnector extends AbstractConnector implements CustomerConnectorInterface
{
    const DEFAULT_SYNC_RANGE = '1 week';

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateInterval */
    protected $syncRange;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $startDate = $this->lastSyncDate;
        $endDate = $this->lastSyncDate->add($this->syncRange);

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

        $data = $this->getCustomersList($filters($startDate, $endDate));

        // move date range, from end to start, allow new customers to be imported first
        $endDate = $startDate;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomersList($filters = [])
    {
        return $this->call(CustomerConnectorInterface::ACTION_CUSTOMER_LIST, $filters);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerData($id, $isAddressesIncluded = false, $isGroupsIncluded = false, $onlyAttributes = [])
    {
        $result = $this->call(CustomerConnectorInterface::ACTION_CUSTOMER_INFO, [$id, $onlyAttributes]);

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
        return $this->call(CustomerConnectorInterface::ACTION_ADDRESS_LIST, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups($groupId = null)
    {
        $result = $this->call(CustomerConnectorInterface::ACTION_GROUP_LIST);

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
        return $this->call(CustomerConnectorInterface::ACTION_STORE_LIST);
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
     * @param Channel $channel
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return $this
     */
    public function setChannel(Channel $channel)
    {
        $channelSettings = $channel->getSettings();
        if (empty($channelSettings['last_sync_date'])) {
            throw new InvalidConfigurationException('Last sync date can\'t be empty');
        } elseif ($channelSettings['last_sync_date'] instanceof \DateTime) {
                $this->lastSyncDate = $channelSettings['last_sync_date'];
        } else {
            $this->lastSyncDate = new \DateTime($channelSettings['last_sync_date']);
        }

        if (empty($channelSettings['sync_range'])) {
            $channelSettings['sync_range'] = self::DEFAULT_SYNC_RANGE;
        } elseif ($channelSettings['sync_range'] instanceof \DateInterval) {
            $this->syncRange = $channelSettings['sync_range'];
        } else {
            $this->syncRange = \DateInterval::createFromDateString($channelSettings['sync_range']);
        }

        return parent::setChannel($channel);
    }
}
