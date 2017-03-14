<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Twig;

use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Twig\MetadataExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class MetadataExtensionTest extends \PHPUnit_Framework_TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var MetadataExtension */
    protected $extension;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder(MetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_channel.provider.metadata_provider', $this->provider)
            ->getContainer($this);

        $this->extension = new MetadataExtension($container);
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

        $this->assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_entities_metadata', [])
        );
    }

    public function testGetChannelTypeMetadata()
    {
        $expectedResult = new \stdClass();

        $this->provider->expects($this->once())
            ->method('getChannelTypeMetadata')
            ->will($this->returnValue($expectedResult));

        $this->assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_type_metadata', [])
        );
    }

    public function testGetName()
    {
        $this->assertEquals($this->extension->getName(), 'oro_channel_metadata');
    }
}
