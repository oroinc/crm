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

    public function testGetMetadataList()
    {
        $this->provider->expects($this->once())
            ->method('getMetadataList');

        $integrationEntities = new MetadataExtension($this->provider);
        $integrationEntities->getListOfIntegrationEntities();
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

        $this->assertArrayHasKey('orocrm_integration_entities', $result);
        $this->assertEquals(
            $result['orocrm_integration_entities'],
            new \Twig_Function_Method($integrationEntities, 'getListOfIntegrationEntities')
        );
    }
}
