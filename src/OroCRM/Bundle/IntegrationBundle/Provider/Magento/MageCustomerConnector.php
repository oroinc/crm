<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\AbstractConnector;

class MageCustomerConnector extends AbstractConnector
{
    /**
     * Get customer list
     *
     * @param array $filters
     * @return array
     */
    public function getCustomersList($filters = [])
    {
        $complexFilter = array(
            'complex_filter' => array(
                array(
                    'key' => 'group_id',
                    'value' => array('key' => 'in', 'value' => '1,3')
                )
            )
        );

        return $this->call('customerCustomerList', $filters);
    }

    public function saveCustomerData()
    {
        return [];
    }
}
