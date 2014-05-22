<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderControllerTest extends AbstractController
{
    /** @var \OroCRM\Bundle\MagentoBundle\Entity\Order */
    public static $order;

    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();

        self::$order = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Order')
            ->findOneByChannel($this->channel);
    }

    protected function getMainEntityId()
    {
        return self::$order->getid();
    }

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
