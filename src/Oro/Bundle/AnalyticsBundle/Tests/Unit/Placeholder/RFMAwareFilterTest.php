<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Placeholder;

use Oro\Bundle\AnalyticsBundle\Placeholder\RFMAwareFilter;

class RFMAwareFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $interface = 'Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface';

    /**
     * @var RFMAwareFilter
     */
    protected $filter;

    protected function setUp(): void
    {
        $this->filter = new RFMAwareFilter($this->interface);
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

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        $channelInvalid = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channelInvalid->expects($this->any())
            ->method('getCustomerIdentity')
            ->will($this->returnValue(new \stdClass()));
        $channelValid = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $customerIdentity = $this->createMock($this->interface);
        $channelValid->expects($this->any())
            ->method('getCustomerIdentity')
            ->will($this->returnValue($customerIdentity));

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

    /**
     * @return array
     */
    public function applicableForViewDataProvider()
    {
        $channelInvalid = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channelInvalid->expects($this->any())
            ->method('getCustomerIdentity')
            ->will($this->returnValue(new \stdClass()));

        $channelValidDisabledRFM = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $customerIdentity = $this->createMock($this->interface);
        $channelValidDisabledRFM->expects($this->any())
            ->method('getCustomerIdentity')
            ->will($this->returnValue($customerIdentity));

        $channelValidEnabledRFM = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $customerIdentity = $this->createMock($this->interface);
        $channelValidEnabledRFM->expects($this->any())
            ->method('getCustomerIdentity')
            ->will($this->returnValue($customerIdentity));
        $channelValidEnabledRFM->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(['rfm_enabled' => true]));

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
