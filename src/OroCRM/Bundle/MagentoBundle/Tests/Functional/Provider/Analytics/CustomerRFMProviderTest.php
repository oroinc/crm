<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Provider\Analytics;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use OroCRM\Bundle\MagentoBundle\Provider\Analytics\CustomerFrequencyProvider;
use OroCRM\Bundle\MagentoBundle\Provider\Analytics\CustomerMonetaryProvider;
use OroCRM\Bundle\MagentoBundle\Provider\Analytics\CustomerRecencyProvider;

/**
 * @dbIsolation
 */
class CustomerRFMProviderTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([
            'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMOrderData',
            'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMCategoryData'
        ]);
    }

    /**
     * @dataProvider dataValue
     * @param string $ref
     * @param array $expectedValue
     */
    public function testAnalyticsProviderValues($ref, array $expectedValue)
    {
        $doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        $customer = $this->getReference($ref);
        $className = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
        $recencyProvider = new CustomerRecencyProvider($doctrineHelper, $className);
        $frequencyProvider = new CustomerFrequencyProvider($doctrineHelper, $className);
        $monetaryProvider = new CustomerMonetaryProvider($doctrineHelper, $className);

        $this->assertEquals($expectedValue['recency'], $recencyProvider->getValue($customer));
        $this->assertEquals($expectedValue['frequency'], $frequencyProvider->getValue($customer));
        $this->assertEquals($expectedValue['monetary'], $monetaryProvider->getValue($customer));
    }

    /**
     * @dataProvider dataAnalytics
     * @param string $ref
     * @param array $expectedIndex
     */
    public function testAnalyticsMetrics($ref, array $expectedIndex)
    {
        $doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        /** @var Customer $customer */
        $customer = $this->getReference($ref);
        $className = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
        $recencyProvider = new CustomerRecencyProvider($doctrineHelper, $className);
        $frequencyProvider = new CustomerFrequencyProvider($doctrineHelper, $className);
        $monetaryProvider = new CustomerMonetaryProvider($doctrineHelper, $className);
        $rfmBuilder = new RFMBuilder($doctrineHelper);
        $rfmBuilder->addProvider($recencyProvider);
        $rfmBuilder->addProvider($frequencyProvider);
        $rfmBuilder->addProvider($monetaryProvider);
        $analitycs = new AnalyticsBuilder();
        $analitycs->addBuilder($rfmBuilder);
        $analitycs->build($customer);

        $this->assertEquals($expectedIndex['recency'], $customer->getRecency());
        $this->assertEquals($expectedIndex['frequency'], $customer->getFrequency());
        $this->assertEquals($expectedIndex['monetary'], $customer->getMonetary());
    }

    /**
     * @return array
     */
    public function dataValue()
    {
        return [
            'Providers Customer 1' => [
                'customerRef' => 'customer',
                'expectedValue' => [
                    'recency' => 2,
                    'frequency' => 2,
                    'monetary' => 31
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataAnalytics()
    {
        return [
            'Analytics Customer 1' => [
                'customerRef' => 'customer',
                'expectedIndex' => [
                    'recency' => 1,
                    'frequency' => 1,
                    'monetary' => 2
                ]
            ]
        ];
    }
}
