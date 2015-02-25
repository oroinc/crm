<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

abstract class AbstractDependencyManager
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

        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
        $store   = $dependencies[SoapTransport::ALIAS_STORES][$result->store_id];
        $website = $dependencies[SoapTransport::ALIAS_WEBSITES][$store['website_id']];

        $result->store_code         = $store['code'];
        $result->store_storename    = $store['name'];
        $result->store_website_id   = $website['id'];
        $result->store_website_code = $website['code'];
        $result->store_website_name = $website['name'];
    }
}
