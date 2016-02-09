<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Provider\Analytics;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
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
     * @param string $dataChannelRef
     * @param array $expectedValue
     */
    public function testAnalyticsProviderValues($dataChannelRef, array $expectedValue)
    {
        $doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        /** @var Channel $dataChannel */
        $dataChannel = $this->getReference($dataChannelRef);
        $className = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
        $recencyProvider = new CustomerRecencyProvider($doctrineHelper, $className);
        $frequencyProvider = new CustomerFrequencyProvider($doctrineHelper, $className);
        $monetaryProvider = new CustomerMonetaryProvider($doctrineHelper, $className);

        $recencyData = $recencyProvider->getValues($dataChannel);
        $this->assertCount(1, $recencyData);
        $frequencyData = $frequencyProvider->getValues($dataChannel);
        $this->assertCount(1, $frequencyData);
        $monetaryData = $monetaryProvider->getValues($dataChannel);
        $this->assertCount(1, $monetaryData);

        foreach ($expectedValue as $customerReference => $data) {
            /** @var Customer $customer */
            $customer = $this->getReference($customerReference);
            $this->assertEquals($data['recency'], $recencyData[$customer->getId()]);
            $this->assertEquals($data['frequency'], $frequencyData[$customer->getId()]);
            $this->assertEquals($data['monetary'], $monetaryData[$customer->getId()]);
        }
    }

    /**
     * @return array
     */
    public function dataValue()
    {
        return [
            'Providers Customer 1' => [
                'dataChannelRef' => 'default_channel',
                'expectedValue' => [
                    'customer' => [
                        'recency' => 2,
                        'frequency' => 2,
                        'monetary' => 22.2, // (15.5 - 4.40) + (15.5 - 4.40)]
                    ],
                ]
            ],
        ];
    }
}
