<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\Routing\RouterInterface;

class MetadataProviderTest extends \PHPUnit\Framework\TestCase
{
    private int $entityId1 = 35;
    private int $entityId2 = 84;

    private array $testConfig = [
        'Oro\Bundle\TestBundle1\Entity\Entity1' => [
            'name'                   => 'Oro\Bundle\TestBundle1\Entity\Entity1',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'OR',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'Oro\Bundle\TestBundle1\Entity\Entity2' => [
            'name'                   => 'Oro\Bundle\TestBundle2\Entity\Entity2',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'Oro\Bundle\TestBundle2\Entity\Entity3' => [
            'name'                   => 'Oro\Bundle\TestBundle2\Entity\Entity3',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
        ],
    ];

    private array $entityConfig1 = [
        'name'         => 'Oro\Bundle\TestBundle1\Entity\Entity1',
        'label'        => 'Entity 1',
        'plural_label' => 'Entities 1',
        'icon'         => '',
    ];

    private array $entityConfig2 = [
        'name'         => 'Oro\Bundle\TestBundle2\Entity\Entity2',
        'label'        => 'Entity 2',
        'plural_label' => 'Entities 2',
        'icon'         => '',
    ];

    private array $entityConfig3 = [
        'name'         => 'Oro\Bundle\TestBundle2\Entity\Entity3',
        'label'        => 'Entity 3',
        'plural_label' => 'Entities 3',
        'icon'         => '',
    ];

    public function testGetEntitiesMetadata()
    {
        $settingsProvider = $this->createMock(SettingsProvider::class);
        $settingsProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn($this->testConfig);

        $entityProvider = $this->createMock(EntityProvider::class);
        $entityProvider->expects($this->exactly(3))
            ->method('getEntity')
            ->willReturnOnConsecutiveCalls($this->entityConfig1, $this->entityConfig2, $this->entityConfig3);

        $extendConfigModel = $this->createMock(ConfigInterface::class);
        $extendConfigModel->expects($this->any())
            ->method('get')
            ->with('owner')
            ->willReturn('Custom');

        $extendProvider = $this->createMock(ConfigProvider::class);
        $extendProvider->expects($this->once())
            ->method('map')
            ->willReturn([]);
        $extendProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn($extendConfigModel);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('getProvider')
            ->with('extend')
            ->willReturn($extendProvider);
        $configManager->expects($this->any())
            ->method('getConfigModelId')
            ->willReturnOnConsecutiveCalls($this->entityId1, $this->entityId2);

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->exactly(4))
            ->method('generate');

        $provider = new MetadataProvider(
            $settingsProvider,
            $entityProvider,
            $configManager,
            $router
        );

        $result = $provider->getEntitiesMetadata();
        for ($i = 1; $i < 3; $i++) {
            $expectedConfig = $this->getExpectedConfig($i);
            $entityName = $expectedConfig['name'];

            $this->assertEquals($expectedConfig, $result[$entityName]);
        }
    }

    private function getExpectedConfig(int $index): array
    {
        $result = $this->{'entityConfig' . $index};
        $result['entity_id'] = $this->{'entityId' . $index};
        $result['edit_link'] = null;
        $result['view_link'] = null;
        $result['type'] = 'Custom';

        return $result;
    }
}
