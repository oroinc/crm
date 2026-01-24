<?php

namespace Oro\Bundle\SalesBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * Manages API operations for B2B customer entities, handling CRUD operations through REST endpoints.
 */
class B2bCustomerApiEntityManager extends ApiEntityManager
{
    #[\Override]
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'shippingAddress' => AddressApiUtils::getAddressConfig(),
                'billingAddress'  => AddressApiUtils::getAddressConfig(),
                'account'         => ['fields' => 'id'],
                'contact'         => ['fields' => 'id'],
                'leads'           => ['fields' => 'id'],
                'opportunities'   => ['fields' => 'id'],
                'owner'           => ['fields' => 'id'],
                'organization'    => ['fields' => 'name']
            ]
        ];

        return $config;
    }
}
