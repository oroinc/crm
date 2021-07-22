<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Twig;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Twig\ChannelExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ChannelExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var MetadataProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $metadataProvider;

    /** @var AmountProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $amountProvider;

    /** @var ChannelExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->amountProvider = $this->createMock(AmountProvider::class);

        $container = self::getContainerBuilder()
            ->add('oro_channel.provider.metadata_provider', $this->metadataProvider)
            ->add('oro_channel.provider.lifetime.amount_provider', $this->amountProvider)
            ->getContainer($this);

        $this->extension = new ChannelExtension($container);
    }

    public function testGetEntitiesMetadata()
    {
        $expectedResult = new \stdClass();

        $this->metadataProvider->expects($this->once())
            ->method('getEntitiesMetadata')
            ->willReturn($expectedResult);

        $this->assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_entities_metadata', [])
        );
    }

    public function testGetChannelTypeMetadata()
    {
        $expectedResult = ['key' => 'value'];

        $this->metadataProvider->expects($this->once())
            ->method('getChannelTypeMetadata')
            ->willReturn($expectedResult);

        $this->assertSame(
            array_flip($expectedResult),
            self::callTwigFunction($this->extension, 'oro_channel_type_metadata', [])
        );
    }

    public function testGetLifetimeValue()
    {
        $expectedResult = 12.33;
        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);

        $this->amountProvider->expects($this->once())
            ->method('getAccountLifeTimeValue')
            ->with($this->identicalTo($account), $this->identicalTo($channel))
            ->willReturn($expectedResult);

        $this->assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_account_lifetime', [$account, $channel])
        );
    }
}
