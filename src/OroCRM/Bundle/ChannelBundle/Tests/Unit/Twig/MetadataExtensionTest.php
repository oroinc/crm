<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Twig;

use OroCRM\Bundle\ChannelBundle\Twig\MetadataExtension;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingProvider;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\MetadataProvider')
            ->disableOriginalConstructor()->getMock();
        $this->settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
    }

    public function testGetEntitiesMetadata()
    {
        $this->provider->expects($this->once())
            ->method('getEntitiesMetadata');

        $integrationEntities = new MetadataExtension($this->provider, $this->settingProvider);
        $integrationEntities->getEntitiesMetadata();
    }

    public function testGetName()
    {
        $integrationEntities = new MetadataExtension($this->provider, $this->settingProvider);
        $this->assertEquals($integrationEntities->getName(), 'orocrm_list_of_integrations_entities');
    }

    public function testGetFunctions()
    {
        $integrationEntities = new MetadataExtension($this->provider, $this->settingProvider);
        $result              = $integrationEntities->getFunctions();

        $this->assertArrayHasKey('orocrm_channel_entities_metadata', $result);
        $this->assertArrayHasKey('orocrm_channel_integration_metadata', $result);
    }

    public function testGetIntegrationEntitiesMetadata()
    {
        $this->provider->expects($this->once())
            ->method('getIntegrationEntities');

        $integrationEntities = new MetadataExtension($this->provider, $this->settingProvider);
        $integrationEntities->getIntegrationEntities();
    }
}
