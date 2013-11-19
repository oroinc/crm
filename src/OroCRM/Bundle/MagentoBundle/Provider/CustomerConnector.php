<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\IntegrationBundle\Entity\Connector;
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
        $endDate = clone $this->lastSyncDate;
        $endDate = $endDate->add($this->syncRange);

        // TODO: remove
        var_dump([$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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

        $this->lastSyncDate = $endDate;

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
     * {@inheritdoc}
     */
    public function setConnectorEntity(Connector $connector)
    {
        $settings = $connector->getTransport()
            ->getSettingsBag()
            ->all();

        if (empty($settings['last_sync_date'])) {
            throw new InvalidConfigurationException('Last sync date can\'t be empty');
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

        return parent::setConnectorEntity($connector);
    }
}
