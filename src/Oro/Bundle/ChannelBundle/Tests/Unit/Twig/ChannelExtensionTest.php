<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Twig;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\ChannelBundle\Provider\MetadataProvider;
use Oro\Bundle\ChannelBundle\Twig\ChannelExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChannelExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private MetadataProvider&MockObject $metadataProvider;
    private AmountProvider&MockObject $amountProvider;
    private ChannelExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->amountProvider = $this->createMock(AmountProvider::class);

        $container = self::getContainerBuilder()
            ->add('oro_channel.provider.metadata_provider', $this->metadataProvider)
            ->add(AmountProvider::class, $this->amountProvider)
            ->getContainer($this);

        $this->extension = new ChannelExtension($container);
    }

    public function testGetEntitiesMetadata(): void
    {
        $expectedResult = ['Test\Entity' => ['key' => 'value']];

        $this->metadataProvider->expects(self::once())
            ->method('getEntitiesMetadata')
            ->willReturn($expectedResult);

        self::assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_entities_metadata', [])
        );
    }

    public function testGetChannelTypeMetadata(): void
    {
        $expectedResult = ['key' => 'value'];

        $this->metadataProvider->expects(self::once())
            ->method('getChannelTypeMetadata')
            ->willReturn($expectedResult);

        self::assertSame(
            array_flip($expectedResult),
            self::callTwigFunction($this->extension, 'oro_channel_type_metadata', [])
        );
    }

    public function testGetLifetimeValue(): void
    {
        $expectedResult = 12.33;
        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);

        $this->amountProvider->expects(self::once())
            ->method('getAccountLifeTimeValue')
            ->with(self::identicalTo($account), self::identicalTo($channel))
            ->willReturn($expectedResult);

        self::assertSame(
            $expectedResult,
            self::callTwigFunction($this->extension, 'oro_channel_account_lifetime', [$account, $channel])
        );
    }
}
