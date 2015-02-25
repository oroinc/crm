<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

class OrderDependencyManager extends AbstractDependencyManager
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

        $result->payment_method = isset($result->payment, $result->payment->method) ? $result->payment->method : null;
    }
}
