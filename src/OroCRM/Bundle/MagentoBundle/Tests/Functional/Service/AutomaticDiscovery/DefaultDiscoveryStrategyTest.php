<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Service\AutomaticDiscovery;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\DependencyInjection\Configuration;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery\DefaultDiscoveryStrategy;

/**
 * @dbIsolation
 */
class DefaultDiscoveryStrategyTest extends WebTestCase
{
    /**
     * @var DefaultDiscoveryStrategy
     */
    protected $strategy;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadAddressDiscoveryData']);

        $this->strategy = $this->getContainer()->get('orocrm_magento.strategy.automatic_discovery.default');
    }

    /**
     * @param string $reference
     * @param string $strategy
     * @param array $expected
     *
     * @dataProvider strategyDataProvider
     */
    public function testApply($reference, array $expected)
    {
        /** @var Customer $entity */
        $entity = $this->getReference($reference);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('OroCRMMagentoBundle:Customer');

        $qb = $repo->createQueryBuilder(AutomaticDiscovery::ROOT_ALIAS);

        $this->strategy->apply(
            $qb,
            AutomaticDiscovery::ROOT_ALIAS,
            'firstName',
            [
                Configuration::DISCOVERY_FIELDS_KEY => ['firstName' => []],
                Configuration::DISCOVERY_STRATEGY_KEY => [],
                Configuration::DISCOVERY_OPTIONS_KEY => [
                    Configuration::DISCOVERY_MATCH_KEY => Configuration::DISCOVERY_MATCH_FIRST,
                    Configuration::DISCOVERY_EMPTY_KEY => false
                ]
            ],
            $entity
        );


        $expected = array_map(
            function ($reference) {
                return $this->getReference($reference);
            },
            $expected
        );

        $this->assertSameSize($expected, $qb->getQuery()->getResult());
        $this->assertEquals($expected, $qb->getQuery()->getResult());
    }

    /**
     * @return array
     */
    public function strategyDataProvider()
    {
        return [
            'match fn1' => [
                'discovery_customer1',
                ['discovery_customer1', 'discovery_customer2']
            ],
            'match fn2' => [
                'discovery_customer3',
                ['discovery_customer3', 'discovery_customer4', 'discovery_customer5']
            ]
        ];
    }
}
