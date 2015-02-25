<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerDependencyManager extends AbstractDependencyManager
{
    /**
     * Adds dependencies to result entity
     *
     * @param \stdClass $result
     * @param array $dependencies
     */
    public static function addDependencyData($result, array $dependencies)
    {
        if (!$result) {
            return;
        }

        // TODO: implement convertion using customer data converter
        //return parent::addDependencyData($result, $dependencies);

        // TODO: remove this after TODO implementation
        $result->group               = $dependencies[SoapTransport::ALIAS_GROUPS][$result->group_id];
        $result->group['originId']   = $result->group['customer_group_id'];
        $result->store               = $dependencies[SoapTransport::ALIAS_STORES][$result->store_id];
        $result->store['originId']   = $result->store_id;
        $result->website             = $dependencies[SoapTransport::ALIAS_WEBSITES][$result->website_id];
        $result->website['originId'] = $result->website['id'];
    }
}
