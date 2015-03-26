<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CustomerDependencyManager extends AbstractDependencyManager
{
    /**
     * {@inheritdoc}
     */
    public static function addDependencyData($result, MagentoTransportInterface $transport)
    {
        if (!$result) {
            return;
        }

        // TODO: implement convertion using customer data converter
        $dependencies = $transport->getDependencies(
            [
                MagentoTransportInterface::ALIAS_GROUPS,
                MagentoTransportInterface::ALIAS_STORES,
                MagentoTransportInterface::ALIAS_WEBSITES
            ]
        );

        // TODO: remove this after TODO implementation
        $result->group = $dependencies[MagentoTransportInterface::ALIAS_GROUPS][$result->group_id];
        $result->group['originId'] = $result->group['customer_group_id'];
        $result->store = $dependencies[MagentoTransportInterface::ALIAS_STORES][$result->store_id];
        $result->store['originId'] = $result->store_id;
        $result->website = $dependencies[MagentoTransportInterface::ALIAS_WEBSITES][$result->website_id];
        $result->website['originId'] = $result->website['id'];
    }
}
