<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\WriterErrorEvent;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Region;

class IntegrationWriteErrorListener
{
    /**
     * @param WriterErrorEvent $event
     */
    public function handleError(WriterErrorEvent $event)
    {
        $warning = '';
        $items   = $event->getBatchItems();

        switch ($event->getJobName()) {
            case 'mage_region_import':
                $entity = 'regions';
                $ids    = array_map(
                    function (Region $item) {
                        return $item->getCombinedCode();
                    },
                    $items
                );

                break;
            case 'mage_order_import':
                $entity = 'orders';
                $ids    = array_map(
                    function (Order $item) {
                        return $item->getIncrementId();
                    },
                    $items
                );

                break;
            case 'mage_customer_import':
            case 'mage_cart_import':
                $entity = $event->getJobName() === 'mage_customer_import' ? 'customers' : 'carts';

                $ids = array_map(
                    function ($item) {
                        return $item->getOriginId();
                    },
                    $items
                );

                break;
            default:
                return;
        }

        $warning .= sprintf('Following %s were not imported: %s', $entity, implode(', ', $ids));

        $event->addWarningText($warning);
        $event->setCouldBeSkipped(true);
    }
}
