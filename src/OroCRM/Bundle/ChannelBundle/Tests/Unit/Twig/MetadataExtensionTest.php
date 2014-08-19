<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Twig;

use OroCRM\Bundle\ChannelBundle\Twig\MetadataExtension;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class MetadataExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var MetadataExtension */
    protected $extension;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\MetadataProvider')
            ->disableOriginalConstructor()->getMock();

        $this->extension = new MetadataExtension($this->provider);
    }

    public function tearDown()
    {
        unset($this->extension, $this->provider);
    }

    public function testGetEntitiesMetadata()
    {
        $expectedResult = new \stdClass();

        $this->provider->expects($this->once())
            ->method('getEntitiesMetadata')
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $this->extension->getEntitiesMetadata());
    }

    public function testGetChannelTypeMetadata()
    {
        $expectedResult = new \stdClass();

        $this->provider->expects($this->once())
            ->method('getChannelTypeMetadata')
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $this->extension->getChannelTypeMetadata());
    }

    public function testGetName()
    {
        $this->assertEquals($this->extension->getName(), 'orocrm_channel_metadata');
    }

    public function testGetFunctions()
    {
        $result = $this->extension->getFunctions();

        $this->assertArrayHasKey('orocrm_channel_entities_metadata', $result);
        $this->assertArrayHasKey('orocrm_channel_type_metadata', $result);
    }
}
