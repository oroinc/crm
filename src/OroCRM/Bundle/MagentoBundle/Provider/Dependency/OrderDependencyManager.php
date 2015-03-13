<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Dependency;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class OrderDependencyManager extends AbstractDependencyManager
{
    /**
     * {@inheritdoc}
     */
    public static function addDependencyData($result, MagentoTransportInterface $transport)
    {
        if (!$result) {
            return;
        }

        parent::addDependencyData($result, $transport);

        $result->payment_method = isset($result->payment, $result->payment->method) ? $result->payment->method : null;
    }
}
