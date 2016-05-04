<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class RFMBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RFMBuilder
     */
    protected $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new RFMBuilder($this->doctrineHelper);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected one of "recency,frequency,monetary" type, "wrong_type" given
     */
    public function testAddProviderFailed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMProviderInterface $provider */
        $provider = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface');
        $provider->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('wrong_type'));

        $this->builder->addProvider($provider);
    }

    public function testAddProviderSuccess()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RFMProviderInterface $provider */
        $provider = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface');
        $provider->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(RFMMetricCategory::TYPE_FREQUENCY));

        $this->builder->addProvider($provider);
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
        $mock = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $mock->expects($this->once())
            ->method('getCustomerIdentity')
            ->willReturn(new RFMAwareStub());
        return [
            [new Channel(), false],
            [$mock, true],
        ];
    }
}
