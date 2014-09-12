<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

use OroCRM\Bundle\ChannelBundle\Model\Condition\ChannelEntityAvailability;

use Symfony\Component\PropertyAccess\PropertyPath;

class ChannelEntityAvailabilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelEntityAvailability
     */
    protected $condition;

    protected function setUp()
    {
        $this->condition = new ChannelEntityAvailability(new ContextAccessor());
    }

    /**
     * @dataProvider isAllowedDataProvider
     *
     * @param array  $options
     * @param string $context
     * @param string $expectedResult
     */
    public function testIsAllowed(array $options, $context, $expectedResult)
    {
        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->isAllowed($context));
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider()
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
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
     * @expectedExceptionMessage Options must have 2 element, but 0 given
     */
    public function testInitializeFailsWhenOptionNotOneElement()
    {
        $this->condition->initialize(array());
    }
}
