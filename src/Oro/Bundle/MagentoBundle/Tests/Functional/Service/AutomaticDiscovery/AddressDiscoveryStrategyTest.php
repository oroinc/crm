<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Service\AutomaticDiscovery;

use Doctrine\Common\Collections\Criteria;
use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery\AddressDiscoveryStrategy;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AddressDiscoveryStrategyTest extends WebTestCase
{
    /**
     * @var AddressDiscoveryStrategy
     */
    protected $strategy;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadAddressDiscoveryData']);

        $this->strategy = $this->getContainer()->get('oro_magento.strategy.automatic_discovery.addresses');
    }

    /**
     * @param string $reference
     * @param string $strategy
     * @param array $expected
     *
     * @dataProvider strategyDataProvider
     */
    public function testApply($reference, $strategy, array $expected)
    {
        /** @var Customer $entity */
        $entity = $this->getReference($reference);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('OroMagentoBundle:Customer');

        $qb = $repo->createQueryBuilder(AutomaticDiscovery::ROOT_ALIAS);

        $this->strategy->apply(
            $qb,
            AutomaticDiscovery::ROOT_ALIAS,
            'addresses',
            [
                Configuration::DISCOVERY_FIELDS_KEY => ['addresses' => ['postalCode' => []], 'email' => []],
                Configuration::DISCOVERY_STRATEGY_KEY => ['addresses' => $strategy],
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

        $result = $qb
            ->addOrderBy(sprintf('%s.lastName', AutomaticDiscovery::ROOT_ALIAS), Criteria::ASC)
            ->getQuery()
            ->getResult();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function strategyDataProvider()
    {
        return [
            'match any to billing' => [
                'discovery_customer1',
                AddressDiscoveryStrategy::STRATEGY_ANY_OF,
                ['discovery_customer1', 'discovery_customer3', 'discovery_customer5']
            ],
            'match any to shipping' => [
                'discovery_customer3',
                AddressDiscoveryStrategy::STRATEGY_ANY_OF,
                ['discovery_customer1', 'discovery_customer3', 'discovery_customer5']
            ],
            'match typed billing' => [
                'discovery_customer1',
                AddressDiscoveryStrategy::STRATEGY_BY_TYPE,
                ['discovery_customer1', 'discovery_customer5']
            ],
            'match typed shipping' => [
                'discovery_customer3',
                AddressDiscoveryStrategy::STRATEGY_BY_TYPE,
                ['discovery_customer3']
            ],
            'match exactly billing to billing' => [
                'discovery_customer1',
                AddressDiscoveryStrategy::STRATEGY_BILLING,
                ['discovery_customer1', 'discovery_customer5']
            ],
            'match exactly shipping to billing' => [
                'discovery_customer3',
                AddressDiscoveryStrategy::STRATEGY_BILLING,
                ['discovery_customer1', 'discovery_customer5']
            ],
            'match exactly billing to shipping' => [
                'discovery_customer1',
                AddressDiscoveryStrategy::STRATEGY_SHIPPING,
                ['discovery_customer3']
            ],
            'match exactly shipping to shipping' => [
                'discovery_customer3',
                AddressDiscoveryStrategy::STRATEGY_SHIPPING,
                ['discovery_customer3']
            ]
        ];
    }
}
