<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Twig;

use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Twig\MetadataExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class MetadataExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $provider;

    /** @var MetadataExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder(MetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_channel.provider.metadata_provider', $this->provider)
            ->getContainer($this);

        $this->extension = new MetadataExtension($container);
    }

    protected function tearDown(): void
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
        $expectedResult = ['key' => 'value'];

        $this->provider->expects($this->once())
            ->method('getChannelTypeMetadata')
            ->will($this->returnValue($expectedResult));

        $this->assertSame(
            array_flip($expectedResult),
            self::callTwigFunction($this->extension, 'oro_channel_type_metadata', [])
        );
    }

    public function testGetName()
    {
        $this->assertEquals($this->extension->getName(), 'oro_channel_metadata');
    }
}
