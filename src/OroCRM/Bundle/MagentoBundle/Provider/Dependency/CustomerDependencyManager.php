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

        $dependencies = $transport->getDependencies([MagentoTransportInterface::ALIAS_GROUPS]);

        $result->group = $dependencies[MagentoTransportInterface::ALIAS_GROUPS][$result->group_id];
        $result->group['originId'] = $result->group['customer_group_id'];
    }
}
