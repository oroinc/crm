<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderControllerTest extends AbstractController
{
    public function gridProvider()
    {
        return [
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-order-grid'
                    ],
                    'gridFilters'    => [],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName' => 'John',
                        'lastName'  => 'Doe',
                        'status'    => 'open',
                        'subTotal'  => '$0.00',
                    ],
                    'oneOrMore'      => true
                ],
            ],
        ];
    }
}
