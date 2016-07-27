<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class OrderApiEntityManager extends ApiEntityManager
{
    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'excluded_fields' => ['relatedEmails'],
            'fields'          => [
                'store'        => ['fields' => 'id'],
                'dataChannel'  => ['fields' => 'id'],
                'channel'      => ['fields' => 'id'],
                'cart'         => ['fields' => 'id'],
                'customer'     => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id'],
                'items'        => ['fields' => [
                    'order'   => ['fields' => 'id'],
                    'channel' => ['fields' => 'id'],
                ]],
                'addresses'    => $this->getAddressSerializationConfig()
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getAddressSerializationConfig()
    {
        return [
            'fields' => [
                'types'   => ['fields' => 'name'],
                'country' => ['fields' => 'iso2Code'],
                'region'  => ['fields' => 'combinedCode'],
                'owner'   => ['fields' => 'id'],
                'channel' => ['fields' => 'id'],
            ]
        ];
    }
}
