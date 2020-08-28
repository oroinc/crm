<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\ChannelBundle\Configuration\ChannelConfigurationProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ChannelConfigurationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var SettingsProvider */
    private $settingsProvider;

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(ChannelConfigurationProvider::class);

        $this->settingsProvider = new SettingsProvider($this->configProvider);
    }

    public function testGetChannelTypes()
    {
        $channelTypes = ['channel1' => []];

        $this->configProvider->expects(self::once())
            ->method('getChannelTypes')
            ->willReturn($channelTypes);

        $this->assertSame($channelTypes, $this->settingsProvider->getChannelTypes());
    }

    public function testGetEntities()
    {
        $entities = ['Test\Entity1' => []];

        $this->configProvider->expects(self::once())
            ->method('getEntities')
            ->willReturn($entities);

        $this->assertSame($entities, $this->settingsProvider->getEntities());
    }

    public function testIsChannelEntity()
    {
        $this->configProvider->expects(self::any())
            ->method('getEntities')
            ->willReturn([
                'Test\Entity1' => []
            ]);

        $this->assertTrue($this->settingsProvider->isChannelEntity('Test\Entity1'));
        $this->assertFalse($this->settingsProvider->isChannelEntity('Test\Entity2'));
    }

    public function testIsCustomerEntity()
    {
        $this->configProvider->expects(self::any())
            ->method('getCustomerEntities')
            ->willReturn(['Test\Entity1']);

        $this->assertTrue($this->settingsProvider->isCustomerEntity('Test\Entity1'));
        $this->assertFalse($this->settingsProvider->isCustomerEntity('Test\Entity2'));
    }

    public function testIsDependentOnChannelEntity()
    {
        $this->configProvider->expects(self::any())
            ->method('getDependentEntitiesMap')
            ->willReturn([
                'Test\Entity1' => ['Test\Entity2']
            ]);

        $this->assertTrue($this->settingsProvider->isDependentOnChannelEntity('Test\Entity1'));
        $this->assertFalse($this->settingsProvider->isDependentOnChannelEntity('Test\Entity2'));
    }

    public function testGetDependentEntities()
    {
        $dependentEntitiesMap = ['Test\Entity1' => ['Test\Entity2']];

        $this->configProvider->expects(self::any())
            ->method('getDependentEntitiesMap')
            ->willReturn($dependentEntitiesMap);

        $this->assertSame(
            $dependentEntitiesMap['Test\Entity1'],
            $this->settingsProvider->getDependentEntities('Test\Entity1')
        );
        $this->assertSame([], $this->settingsProvider->getDependentEntities('Test\Entity2'));
    }

    public function testGetSourceIntegrationTypes()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['integration_type' => 'integration1'],
                'channel2' => ['integration_type' => 'integration1'],
                'channel3' => ['integration_type' => 'integration2'],
                'channel4' => []
            ]);

        $this->assertSame(
            ['integration1', 'integration2'],
            $this->settingsProvider->getSourceIntegrationTypes()
        );
    }

    public function testGetChannelTypeChoiceList()
    {
        $this->configProvider->expects(self::once())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => [
                    'label'    => 'Channel 1',
                    'priority' => 0
                ],
                'channel2' => [
                    'label'    => 'Channel 2',
                    'priority' => -10
                ]
            ]);

        $this->assertSame(
            ['Channel 2' => 'channel2', 'Channel 1' => 'channel1'],
            $this->settingsProvider->getChannelTypeChoiceList()
        );
    }

    public function testGetNonSystemChannelTypeChoiceList()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => [
                    'label'    => 'Channel 1',
                    'priority' => 0
                ],
                'channel2' => [
                    'label'    => 'Channel 2',
                    'priority' => -10,
                    'system'   => false
                ],
                'channel3' => [
                    'label'    => 'Channel 3',
                    'priority' => 0,
                    'system'   => true
                ]
            ]);

        $this->assertSame(
            ['Channel 2' => 'channel2', 'Channel 1' => 'channel1'],
            $this->settingsProvider->getNonSystemChannelTypeChoiceList()
        );
    }

    public function testGetIntegrationType()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['integration_type' => 'integration1'],
                'channel2' => ['integration_type' => 'integration1'],
                'channel3' => ['integration_type' => 'integration2'],
                'channel4' => []
            ]);

        $this->assertEquals('integration1', $this->settingsProvider->getIntegrationType('channel1'));
        $this->assertEquals('integration1', $this->settingsProvider->getIntegrationType('channel2'));
        $this->assertEquals('integration2', $this->settingsProvider->getIntegrationType('channel3'));
        $this->assertNull($this->settingsProvider->getIntegrationType('channel4'));
    }

    public function testGetIntegrationTypeForUnknownChannel()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The channel "channel2" is not defined.');

        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['integration_type' => 'integration1']
            ]);

        $this->settingsProvider->getIntegrationType('channel2');
    }

    public function testIsSystemChannel()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['system' => true],
                'channel2' => ['system' => false],
                'channel3' => []
            ]);

        $this->assertTrue($this->settingsProvider->isSystemChannel('channel1'));
        $this->assertFalse($this->settingsProvider->isSystemChannel('channel2'));
        $this->assertFalse($this->settingsProvider->isSystemChannel('channel3'));
    }

    public function testIsSystemChannelForUnknownChannel()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The channel "channel2" is not defined.');

        $this->configProvider->expects(self::once())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => []
            ]);

        $this->settingsProvider->isSystemChannel('channel2');
    }

    public function testGetIntegrationConnectorName()
    {
        $this->configProvider->expects(self::any())
            ->method('getEntities')
            ->willReturn([
                'Test\Entity1' => [
                    'belongs_to' => [
                        'connector' => 'connector1'
                    ]
                ],
                'Test\Entity2' => []
            ]);

        $this->assertEquals('connector1', $this->settingsProvider->getIntegrationConnectorName('Test\Entity1'));
        $this->assertNull($this->settingsProvider->getIntegrationConnectorName('Test\Entity2'));
        $this->assertNull($this->settingsProvider->getIntegrationConnectorName('Test\Entity3'));
    }

    public function testGetCustomerIdentityFromConfig()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['customer_identity' => 'Test\CustomerIdentity'],
                'channel2' => []
            ]);

        $this->assertEquals(
            'Test\CustomerIdentity',
            $this->settingsProvider->getCustomerIdentityFromConfig('channel1')
        );
        $this->assertNull($this->settingsProvider->getCustomerIdentityFromConfig('channel2'));
        $this->assertNull($this->settingsProvider->getCustomerIdentityFromConfig('channel3'));
    }

    public function testGetEntitiesByChannelType()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['entities' => ['Test\Entity']],
                'channel2' => []
            ]);

        $this->assertEquals(
            ['Test\Entity'],
            $this->settingsProvider->getEntitiesByChannelType('channel1')
        );
        $this->assertSame([], $this->settingsProvider->getEntitiesByChannelType('channel2'));
        $this->assertSame([], $this->settingsProvider->getEntitiesByChannelType('channel3'));
    }

    public function testGetLifetimeValueSettings()
    {
        $this->configProvider->expects(self::any())
            ->method('getChannelTypes')
            ->willReturn([
                'channel1' => ['lifetime_value' => 1, 'customer_identity' => 'Test\CustomerIdentity1'],
                'channel2' => ['lifetime_value' => 2, 'customer_identity' => 'Test\CustomerIdentity2'],
                'channel3' => ['customer_identity' => 'Test\CustomerIdentity3'],
                'channel4' => []
            ]);

        $this->assertEquals(
            [
                'channel1' => ['entity' => 'Test\CustomerIdentity1', 'field' => 1],
                'channel2' => ['entity' => 'Test\CustomerIdentity2', 'field' => 2]
            ],
            $this->settingsProvider->getLifetimeValueSettings()
        );
    }
}
