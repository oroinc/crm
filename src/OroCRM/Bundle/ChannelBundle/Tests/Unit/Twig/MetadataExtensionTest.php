<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Twig;

use OroCRM\Bundle\ChannelBundle\Twig\MetadataExtension;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\MetadataProvider')
            ->disableOriginalConstructor()->getMock();
    }

    public function testGetEntitiesMetadata()
    {
        $this->provider->expects($this->once())
            ->method('getEntitiesMetadata');

        $integrationEntities = new MetadataExtension($this->provider);
        $integrationEntities->getEntitiesMetadata();
    }

    public function testGetName()
    {
        $integrationEntities = new MetadataExtension($this->provider);
        $this->assertEquals($integrationEntities->getName(), 'orocrm_list_of_integrations_entities');
    }

    public function testGetFunctions()
    {
        $integrationEntities = new MetadataExtension($this->provider);
        $result              = $integrationEntities->getFunctions();

        $this->assertArrayHasKey('orocrm_channel_entities_metadata', $result);
        $this->assertArrayHasKey('orocrm_channel_integration_metadata', $result);
    }
}
