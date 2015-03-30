<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class NewsletterSubscriberDependencyManager
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
            MagentoTransportInterface::ALIAS_STORES
        ]);

        $result->store = $dependencies[MagentoTransportInterface::ALIAS_STORES][$result->store_id];
    }
}
