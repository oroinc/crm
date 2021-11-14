<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class RFMBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var RFMBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->builder = new RFMBuilder([], $this->doctrineHelper);
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(Channel $entity, bool $expected)
    {
        $this->assertSame($expected, $this->builder->supports($entity));
    }

    public function supportsDataProvider(): array
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('getCustomerIdentity')
            ->willReturn(new RFMAwareStub());

        return [
            [new Channel(), false],
            [$channel, true],
        ];
    }
}
