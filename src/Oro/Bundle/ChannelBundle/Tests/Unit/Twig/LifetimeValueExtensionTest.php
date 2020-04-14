<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Twig;

use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\ChannelBundle\Twig\LifetimeValueExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class LifetimeValueExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var AmountProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $provider;

    /** @var LifetimeValueExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder(AmountProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_channel.provider.lifetime.amount_provider', $this->provider)
            ->getContainer($this);

        $this->extension = new LifetimeValueExtension($container);
    }

    protected function tearDown(): void
    {
        unset($this->extension, $this->provider);
    }

    public function testGetLifetimeValue()
    {
        $expectedResult = 12.33;
        $account        = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel        = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        $this->provider->expects($this->once())->method('getAccountLifeTimeValue')
            ->with($this->equalTo($account), $this->equalTo($channel))
            ->will($this->returnValue($expectedResult));

        $this->assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_account_lifetime', [$account, $channel])
        );
    }

    public function testGetName()
    {
        $this->assertEquals($this->extension->getName(), 'oro_channel_lifetime_value');
    }
}
