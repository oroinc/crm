<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Bundle\ChannelBundle\Model\Condition\ChannelEntityAvailability;

class ChannelEntityAvailabilityTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelEntityAvailability */
    protected $condition;

    protected function setUp()
    {
        $stateProvider   = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()->getMock();
        $this->condition = new ChannelEntityAvailability($stateProvider);
        $this->condition->setContextAccessor(new ContextAccessor());
    }

    /**
     * @dataProvider evaluateProvider
     *
     * @param array  $options
     * @param string $expectedResult
     */
    public function testEvaluate(array $options, $expectedResult)
    {
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $channel
            ->expects($this->once())
            ->method('getEntities')
            ->willReturn(
                [
                    'Oro\Bundle\SalesBundle\Entity\Lead',
                    'Oro\Bundle\SalesBundle\Entity\Opportunity'
                ]
            );

        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->evaluate(['channel' => $channel]));
    }

    /**
     * @return array
     */
    public function evaluateProvider()
    {
        return [
            'full occurrence'     => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        'Oro\Bundle\SalesBundle\Entity\Lead',
                        'Oro\Bundle\SalesBundle\Entity\Opportunity'
                    ]
                ],
                'expectedResult' => true
            ],
            'not full occurrence' => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        'Oro\Bundle\SalesBundle\Entity\Opportunity',
                        'Oro\Bundle\SalesBundle\Entity\SalesFunnel'
                    ]
                ],
                'expectedResult' => false
            ]
        ];
    }

    /**
     * @expectedException \Oro\Component\ConfigExpression\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid options count: 0
     */
    public function testInitializeFailsWhenOptionNotOneElement()
    {
        $this->condition->initialize(array());
    }
}
