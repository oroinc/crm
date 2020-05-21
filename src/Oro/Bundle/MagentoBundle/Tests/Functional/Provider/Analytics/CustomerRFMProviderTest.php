<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Provider\Analytics;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Analytics\CustomerFrequencyProvider;
use Oro\Bundle\MagentoBundle\Provider\Analytics\CustomerMonetaryProvider;
use Oro\Bundle\MagentoBundle\Provider\Analytics\CustomerRecencyProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerRFMProviderTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMOrderData',
            'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMCategoryData'
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
        $settings = $this->getContainer()->get('oro_channel.provider.settings_provider');
        /** @var Channel $dataChannel */
        $dataChannel = $this->getReference($dataChannelRef);
        $className = 'Oro\Bundle\MagentoBundle\Entity\Customer';
        $recencyProvider = new CustomerRecencyProvider($doctrineHelper, $settings, $className);
        $frequencyProvider = new CustomerFrequencyProvider($doctrineHelper, $settings, $className);
        $monetaryProvider = new CustomerMonetaryProvider($doctrineHelper, $settings, $className);

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
