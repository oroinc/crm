<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $entityId1 = 35;

    /** @var int */
    protected $entityId2 = 84;

    /** @var array */
    protected $testConfig = [
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

    /** @var array */
    protected $entityConfig1 = [
        'name'         => 'Oro\Bundle\TestBundle1\Entity\Entity1',
        'label'        => 'Entity 1',
        'plural_label' => 'Entities 1',
        'icon'         => '',
    ];

    /** @var array */
    protected $entityConfig2 = [
        'name'         => 'Oro\Bundle\TestBundle2\Entity\Entity2',
        'label'        => 'Entity 2',
        'plural_label' => 'Entities 2',
        'icon'         => '',
    ];

    /** @var array */
    protected $entityConfig3 = [
        'name'         => 'Oro\Bundle\TestBundle2\Entity\Entity3',
        'label'        => 'Entity 3',
        'plural_label' => 'Entities 3',
        'icon'         => '',
    ];

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var  EntityProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityProvider;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $configManager;

    /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    public function setUp()
    {
        $this->settingsProvider   = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->settingsProvider->expects($this->once())
            ->method('getSettings')
            ->will($this->returnValue($this->testConfig));

        $this->entityProvider     = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();
        $this->configManager      = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()->getMock();

        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset(
            $this->router,
            $this->entityProvider,
            $this->configManager,
            $this->settingsProvider
        );
    }

    public function testGetEntitiesMetadata()
    {
        $this->entityProvider->expects($this->at(0))
            ->method('getEntity')
            ->will($this->returnValue($this->entityConfig1));
        $this->entityProvider->expects($this->at(1))
            ->method('getEntity')
            ->will($this->returnValue($this->entityConfig2));
        $this->entityProvider->expects($this->at(2))
            ->method('getEntity')
            ->will($this->returnValue($this->entityConfig3));

        $extendConfigModel = $this->createMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $extendConfigModel->expects($this->any())
            ->method('get')
            ->with($this->equalTo('owner'))
            ->will($this->returnValue('Custom'));

        $extendProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()->getMock();
        $extendProvider->expects($this->once())
            ->method('map')
            ->will($this->returnValue([]));
        $extendProvider->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($extendConfigModel));

        $this->configManager->expects($this->any())
            ->method('getProvider')
            ->with($this->equalTo('extend'))
            ->will($this->returnValue($extendProvider));
        $this->configManager->expects($this->any())
            ->method('getConfigModelId')
            ->will($this->onConsecutiveCalls($this->entityId1, $this->entityId2));

        $this->router->expects($this->exactly(4))
            ->method('generate');

        /** @var MetadataProvider $provider */
        $provider = new MetadataProvider(
            $this->settingsProvider,
            $this->entityProvider,
            $this->configManager,
            $this->router
        );

        $result = $provider->getEntitiesMetadata();
        for ($i = 1; $i < 3; $i++) {
            $expectedConfig = $this->getExpectedConfig($i);
            $entityName     = $expectedConfig['name'];

            $this->assertEquals($expectedConfig, $result[$entityName]);
        }
    }

    /**
     * @param $index
     *
     * @return array
     */
    protected function getExpectedConfig($index)
    {
        $configName          = 'entityConfig' . $index;
        $entityId            = 'entityId' . $index;
        $result              = $this->$configName;
        $result['entity_id'] = $this->$entityId;
        $result['edit_link'] = null;
        $result['view_link'] = null;
        $result['type']      = 'Custom';

        return $result;
    }
}
