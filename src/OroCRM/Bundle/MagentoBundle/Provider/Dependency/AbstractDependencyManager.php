<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractDependencyManager
{
    /**
     * Adds dependencies to result entity
     *
     * @param \stdClass $result
     * @param MagentoTransportInterface $transport
     */
    public static function addDependencyData($result, MagentoTransportInterface $transport)
    {
        if (!$result) {
            return;
        }

        $dependencies = $transport->getDependencies([
            MagentoTransportInterface::ALIAS_STORES,
            MagentoTransportInterface::ALIAS_WEBSITES
        ]);

        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
        $store   = $dependencies[MagentoTransportInterface::ALIAS_STORES][$result->store_id];
        $website = $dependencies[MagentoTransportInterface::ALIAS_WEBSITES][$store['website_id']];

        $result->store_code         = $store['code'];
        $result->store_storename    = $store['name'];
        $result->store_website_id   = $website['id'];
        $result->store_website_code = $website['code'];
        $result->store_website_name = $website['name'];
    }
}
