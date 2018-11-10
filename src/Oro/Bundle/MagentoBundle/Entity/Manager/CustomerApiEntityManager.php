<?php

namespace Oro\Bundle\MagentoBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * The API manager for Customer entity.
 */
class CustomerApiEntityManager extends ApiEntityManager
{
    /**
     * @return array
     */
    protected function getSerializationConfig()
    {
        return [
            'fields' => [
                'carts'        => ['exclude' => true],
                'orders'       => ['exclude' => true],
                'newsletterSubscribers' => ['exclude' => true],
                'birthday'     => [
                    'data_transformer' => 'oro_magento.customer_birthday_type_transformer'
                ],
                'website'      => ['fields' => 'id'],
                'store'        => ['fields' => 'id'],
                'group'        => ['fields' => 'id'],
                'contact'      => ['fields' => 'id'],
                'account'      => ['fields' => 'id'],
                'dataChannel'  => ['fields' => 'id'],
                'channel'      => ['fields' => 'id'],
                'owner'        => ['fields' => 'id'],
                'organization' => ['fields' => 'id'],
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
                'newsletterSubscribers' => ['exclude' => true],
                'country' => ['fields' => 'iso2Code'],
                'region'  => ['fields' => 'combinedCode'],
                'owner'   => ['fields' => 'id'],
                'created' => null,
                'updated' => null,
                'types'   => ['fields' => 'name'],
                'channel' => ['fields' => 'id'],
            ]
        ];
    }
}
