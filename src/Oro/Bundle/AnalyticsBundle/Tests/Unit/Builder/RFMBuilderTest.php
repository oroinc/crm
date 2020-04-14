<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class RFMBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var RFMBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->builder = new RFMBuilder([], $this->doctrineHelper);
    }

    /**
     * @param mixed $entity
     * @param bool $expected
     *
     * @dataProvider supportsDataProvider
     */
    public function testSupports($entity, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->builder->supports($entity)
        );
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        $mock = $this->createMock(Channel::class);
        $mock->expects($this->once())
            ->method('getCustomerIdentity')
            ->willReturn(new RFMAwareStub());

        return [
            [new Channel(), false],
            [$mock, true],
        ];
    }
}
