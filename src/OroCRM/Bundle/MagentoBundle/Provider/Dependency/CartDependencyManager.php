<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CartDependencyManager
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
        if (empty($result->customer_group_id)) {
            $groupId = false;

            foreach ($dependencies[MagentoTransportInterface::ALIAS_GROUPS] as $group) {
                if (CartsBridgeIterator::NOT_LOGGED_IN === $group['customer_group_code']) {
                    $groupId = $group['id'];
                    break;
                }
            }
            unset($group);

            if (false === $groupId) {
                reset($dependencies[MagentoTransportInterface::ALIAS_GROUPS]);
                $currentElement = current($dependencies[MagentoTransportInterface::ALIAS_GROUPS]);

                if (!empty($currentElement)) {
                    $groupId = $currentElement['id'];
                }
            }
        } else {
            $groupId = $result->customer_group_id;
        }

        $customer_group              = $dependencies[MagentoTransportInterface::ALIAS_GROUPS][$groupId];
        $result->customer_group_code = $customer_group['customer_group_code'];
        $result->customer_group_name = $customer_group['name'];
    }
}
