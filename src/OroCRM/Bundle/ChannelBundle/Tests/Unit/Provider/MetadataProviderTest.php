<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use OroCRM\Bundle\ChannelBundle\Provider\MetadataProvider;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var int */
    protected $entityId1 = 35;

    /** @var int */
    protected $entityId2 = 84;

    /** @var array */
    protected $expected = [
        'OroCRM\Bundle\TestBundle1\Entity\Entity1' => [
            'name'                   => 'OroCRM\Bundle\TestBundle1\Entity\Entity1',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'OR',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'OroCRM\Bundle\TestBundle1\Entity\Entity2' => [
            'name'                   => 'OroCRM\Bundle\TestBundle2\Entity\Entity2',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
            'belongs_to'             => ['integration' => 'testIntegrationType']
        ],
        'OroCRM\Bundle\TestBundle2\Entity\Entity3' => [
            'name'                   => 'OroCRM\Bundle\TestBundle2\Entity\Entity3',
            'dependent'              => [],
            'navigation_items'       => [],
            'dependencies'           => [],
            'dependencies_condition' => 'AND',
        ],
    ];

    /** @var array */
    protected $entityConfig1 = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity1',
        'label'        => 'Entity 1',
        'plural_label' => 'Entities 1',
        'icon'         => '',
    ];

    /** @var array */
    protected $entityConfig2 = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity2',
        'label'        => 'Entity 2',
        'plural_label' => 'Entities 2',
        'icon'         => '',
    ];

    /** @var array */
    protected $entityConfig3 = [
        'name'         => 'OroCRM\Bundle\TestBundle1\Entity\Entity3',
        'label'        => 'Entity 3',
        'plural_label' => 'Entities 3',
        'icon'         => ''
    ];

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var  EntityProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityProvider;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $configManager;

    /** @var EntityConfigModel|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityConfigModel1;

    /** @var EntityConfigModel|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityConfigModel2;

    /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    public function setUp()
    {
        $this->settingsProvider   = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->entityProvider     = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();
        $this->configManager      = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()->getMock();
        $this->entityConfigModel1 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel')
            ->disableOriginalConstructor()->getMock();
        $this->entityConfigModel2 = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel')
            ->disableOriginalConstructor()->getMock();

        $this->entityConfigModel1->expects($this->any())
            ->method('getId')
            ->will($this->returnvalue($this->entityId1));

        $this->entityConfigModel2->expects($this->any())
            ->method('getId')
            ->will($this->returnvalue($this->entityId2));

        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()->getMock();
    }

    public function testGetListOfIntegrationEntities()
    {
        $this->settingsProvider->expects($this->once())
            ->method('getSettings')
            ->will($this->returnvalue($this->expected));

        $this->entityProvider->expects($this->at(0))
            ->method('getEntity')
            ->will($this->returnvalue($this->entityConfig1));
        $this->entityProvider->expects($this->at(1))
            ->method('getEntity')
            ->will($this->returnvalue($this->entityConfig2));

        $this->configManager->expects($this->at(0))
            ->method('getConfigEntityModel')
            ->will($this->returnvalue($this->entityConfigModel1));
        $this->configManager->expects($this->at(1))
            ->method('getConfigEntityModel')
            ->will($this->returnvalue($this->entityConfigModel2));

        $this->router->expects($this->exactly(4))
            ->method('generate');

        /** @var MetadataProvider $provider */
        $provider = new MetadataProvider(
            $this->settingsProvider,
            $this->entityProvider,
            $this->configManager,
            $this->router
        );
        $result   = $provider->getMetadataList();

        $this->assertArrayHasKey('testIntegrationType', $result);
        $this->assertCount(2, $result['testIntegrationType']);

        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($result['testIntegrationType'][$i], $this->getConfig($i));
        }
    }

    /**
     * @param $index
     *
     * @return array
     */
    protected function getConfig($index)
    {
        $configName          = 'entityConfig' . ($index + 1);
        $entityId            = 'entityId' . ($index + 1);
        $result              = $this->$configName;
        $result['entity_id'] = $this->$entityId;
        $result['edit_link'] = null;
        $result['view_link'] = null;

        return $result;
    }
}
