<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Component\ConfigExpression\ContextAccessor;

use OroCRM\Bundle\ChannelBundle\Model\Condition\ChannelEntityAvailability;

class ChannelEntityAvailabilityTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelEntityAvailability */
    protected $condition;

    protected function setUp()
    {
        $stateProvider   = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()->getMock();
        $this->condition = new ChannelEntityAvailability($stateProvider);
        $this->condition->setContextAccessor(new ContextAccessor());
    }

    /**
     * @dataProvider evaluateProvider
     *
     * @param array  $options
     * @param string $context
     * @param string $expectedResult
     */
    public function testEvaluate(array $options, $context, $expectedResult)
    {
        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->evaluate($context));
    }

    /**
     * @return array
     */
    public function evaluateProvider()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $channel
            ->expects($this->exactly(2))
            ->method('getEntities')
            ->will(
                $this->returnValue(
                    [
                        'OroCRM\Bundle\SalesBundle\Entity\Lead',
                        'OroCRM\Bundle\SalesBundle\Entity\Opportunity'
                    ]
                )
            );

        return [
            'full occurrence'     => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        'OroCRM\Bundle\SalesBundle\Entity\Lead',
                        'OroCRM\Bundle\SalesBundle\Entity\Opportunity'
                    ]
                ],
                'context'        => ['channel' => $channel],
                'expectedResult' => true
            ],
            'not full occurrence' => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                        'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel'
                    ]
                ],
                'context'        => ['channel' => $channel],
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
