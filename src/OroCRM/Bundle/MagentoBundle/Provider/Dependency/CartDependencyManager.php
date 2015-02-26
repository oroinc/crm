<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CartDependencyManager extends AbstractDependencyManager
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

        parent::addDependencyData($result, $dependencies);

        if (empty($result->customer_group_id)) {
            $groupId = false;

            foreach ($dependencies[SoapTransport::ALIAS_GROUPS] as $group) {
                if (CartsBridgeIterator::NOT_LOGGED_IN === $group['customer_group_code']) {
                    $groupId = $group['id'];
                    break;
                }
            }
            unset($group);

            if (false === $groupId) {
                reset($dependencies[SoapTransport::ALIAS_GROUPS]);
                $currentElement = current($dependencies[SoapTransport::ALIAS_GROUPS]);

                if (!empty($currentElement)) {
                    $groupId = $currentElement['id'];
                }
            }
        } else {
            $groupId = $result->customer_group_id;
        }

        $customer_group              = $dependencies[SoapTransport::ALIAS_GROUPS][$groupId];
        $result->customer_group_code = $customer_group['customer_group_code'];
        $result->customer_group_name = $customer_group['name'];
    }
}
