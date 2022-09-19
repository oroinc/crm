<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Model\Condition\ChannelEntityAvailability;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyPath;

class ChannelEntityAvailabilityTest extends \PHPUnit\Framework\TestCase
{
    /** @var ChannelEntityAvailability */
    private $condition;

    protected function setUp(): void
    {
        $stateProvider = $this->createMock(StateProvider::class);

        $this->condition = new ChannelEntityAvailability($stateProvider);
        $this->condition->setContextAccessor(new ContextAccessor());
    }

    /**
     * @dataProvider evaluateProvider
     */
    public function testEvaluate(array $options, bool $expectedResult): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->once())
            ->method('getEntities')
            ->willReturn([Lead::class, Opportunity::class]);

        $this->condition->initialize($options);
        $this->assertEquals($expectedResult, $this->condition->evaluate(['channel' => $channel]));
    }

    public function evaluateProvider(): array
    {
        return [
            'full occurrence'     => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        Lead::class,
                        Opportunity::class
                    ]
                ],
                'expectedResult' => true
            ],
            'not full occurrence' => [
                'options'        => [
                    new PropertyPath('[channel]'),
                    [
                        Opportunity::class,
                        User::class
                    ]
                ],
                'expectedResult' => false
            ]
        ];
    }

    public function testInitializeFailsWhenOptionNotOneElement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options count: 0');

        $this->condition->initialize([]);
    }
}
