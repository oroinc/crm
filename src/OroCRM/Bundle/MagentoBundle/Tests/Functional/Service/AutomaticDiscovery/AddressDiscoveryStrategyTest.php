<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Service\AutomaticDiscovery;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery\AddressDiscoveryStrategy;

class AddressDiscoveryStrategyTest extends WebTestCase
{
    /**
     * @var AddressDiscoveryStrategy
     */
    protected $strategy;

    protected function setUp()
    {
        $this->initClient();

        $this->strategy = $this->getContainer()->get('orocrm_magento.strategy.automatic_discovery.addresses');
    }

    /**
     * @param string $strategy
     * @param array $expected
     *
     * @strategyDataProvider
     */
    public function testApply($strategy, array $expected)
    {
        $entity = null;

        $em = $this->getContainer()->get('doctrine')->getRepository('OroCRMMagentoBundle:Customer');
        $qb = $em->createQueryBuilder('c');

        $this->strategy->apply(
            $qb,
            AutomaticDiscovery::ROOT_ALIAS,
            'addresses',
            [
                'fields' => ['addresses' => ['postalCode']],
                'strategy' => ['addresses' => $strategy]
            ],
            $entity
        );

        $this->assertEquals($expected, $qb->getQuery()->getResult());
    }

    /**
     * @return array
     */
    public function strategyDataProvider()
    {
        return [];
    }
}
