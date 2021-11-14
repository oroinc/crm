<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Placeholder;

use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\AnalyticsBundle\Placeholder\RFMAwareFilter;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class RFMAwareFilterTest extends \PHPUnit\Framework\TestCase
{
    private const INTERFACE = AnalyticsAwareInterface::class;

    /** @var RFMAwareFilter */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new RFMAwareFilter(self::INTERFACE);
    }

    /**
     * @dataProvider applicableDataProvider
     * @param object $entity
     * @param bool $expected
     */
    public function testIsApplicable($entity, $expected)
    {
        $this->assertEquals($expected, $this->filter->isApplicable($entity));
    }

    public function applicableDataProvider(): array
    {
        $channelInvalid = $this->createMock(Channel::class);
        $channelInvalid->expects($this->any())
            ->method('getCustomerIdentity')
            ->willReturn(new \stdClass());
        $channelValid = $this->createMock(Channel::class);
        $customerIdentity = $this->createMock(self::INTERFACE);
        $channelValid->expects($this->any())
            ->method('getCustomerIdentity')
            ->willReturn($customerIdentity);

        return [
            'not a channel' => [
                new \stdClass(),
                false
            ],
            'not applicable customer identity' => [
                $channelInvalid,
                false
            ],
            'applicable customer identity' => [
                $channelValid,
                true
            ],
        ];
    }

    /**
     * @dataProvider applicableForViewDataProvider
     * @param object $entity
     * @param bool $expected
     */
    public function testIsViewApplicable($entity, $expected)
    {
        $this->assertEquals($expected, $this->filter->isViewApplicable($entity));
    }

    public function applicableForViewDataProvider(): array
    {
        $channelInvalid = $this->createMock(Channel::class);
        $channelInvalid->expects($this->any())
            ->method('getCustomerIdentity')
            ->willReturn(new \stdClass());

        $channelValidDisabledRFM = $this->createMock(Channel::class);
        $customerIdentity = $this->createMock(self::INTERFACE);
        $channelValidDisabledRFM->expects($this->any())
            ->method('getCustomerIdentity')
            ->willReturn($customerIdentity);

        $channelValidEnabledRFM = $this->createMock(Channel::class);
        $customerIdentity = $this->createMock(self::INTERFACE);
        $channelValidEnabledRFM->expects($this->any())
            ->method('getCustomerIdentity')
            ->willReturn($customerIdentity);
        $channelValidEnabledRFM->expects($this->any())
            ->method('getData')
            ->willReturn(['rfm_enabled' => true]);

        return [
            'not a channel' => [
                new \stdClass(),
                false
            ],
            'not applicable customer identity' => [
                $channelInvalid,
                false
            ],
            'applicable customer identity disabled rfm' => [
                $channelValidDisabledRFM,
                false
            ],
            'applicable customer identity enabled rfm' => [
                $channelValidEnabledRFM,
                true
            ],
        ];
    }
}
