<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\AbstractConnector;

class MageCustomerConnector extends AbstractConnector implements MagentoCustomerConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function sync($oneWay = self::SYNC_DIRECTION_PULL, $params = [])
    {
        $channelSettings = $this->channel->getSettings();

        $startDate = ''; // initial start date should be taken from channel config data
        $endDate = '';   // should be taken from channel config data too

        $filters = array(array(
            'complex_filter' => array(
                array(
                    'key' => 'created_at',
                    'value' => array(
                        'key' => 'gteq',
                        'value' => $startDate . ' 00:00:00'
                    ),
                ),
                array(
                    'key' => 'created_at',
                    'value' => array(
                        'key' => 'lt',
                        'value' => $endDate . ' 00:00:00'
                    ),
                ),
            )
        ));

        $batchData = $this->getCustomersList($filters);
        $this->processSyncBatch($batchData);
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
