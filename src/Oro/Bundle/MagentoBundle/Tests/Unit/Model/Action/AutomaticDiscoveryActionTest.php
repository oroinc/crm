<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Model\Action;

use Oro\Bundle\MagentoBundle\Model\Action\AutomaticDiscoveryAction;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;

class AutomaticDiscoveryActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AutomaticDiscovery
     */
    protected $automaticDiscovery;

    /**
     * @var AutomaticDiscoveryAction
     */
    protected $action;

    protected function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();
        $this->automaticDiscovery = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery')
            ->disableOriginalConstructor()
            ->getMock();

        $this->action = new AutomaticDiscoveryAction($this->contextAccessor, $this->automaticDiscovery);

        /** @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcher $dispatcher */
        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->action->setDispatcher($dispatcher);
    }

    protected function tearDown(): void
    {
        unset($this->contextAccessor, $this->automaticDiscovery, $this->action);
    }

    /**
     * @param array $options
     * @param string $expectedExceptionMessage
     *
     * @dataProvider invalidOptionsDataProvider
     */
    public function testInitializeErrors(array $options, $expectedExceptionMessage)
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->action->initialize($options);
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider()
    {
        return [
            [[], 'Parameter "entity" is required.'],
            [['entity' => new \stdClass()], 'Parameter "attribute" is required.']
        ];
    }

    public function testInitialize()
    {
        $options = [
            'entity' => new \stdClass(),
            'attribute' => 'c'
        ];

        $this->assertSame($this->action, $this->action->initialize($options));
    }

    /**
     * @param array $options
     * @param mixed $context
     * @param string $expectedExceptionMessage
     *
     * @dataProvider invalidExecuteOptionsDataProvider
     */
    public function testExecuteExceptions(array $options, $context, $expectedExceptionMessage)
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->action->initialize($options);
        $this->action->execute($context);
    }

    /**
     * @return array
     */
    public function invalidExecuteOptionsDataProvider()
    {
        return [
            'empty entity' => [
                [
                    'entity' => new PropertyPath('[entity]'),
                    'attribute' => new PropertyPath('[attribute]')
                ],
                [
                    'entity' => null
                ],
                'Action "automatic_discovery" expects object in parameter "entity", NULL is given.'
            ],
            'invalid entity' => [
                [
                    'entity' => new PropertyPath('[entity]'),
                    'attribute' => new PropertyPath('[attribute]')
                ],
                [
                    'entity' => []
                ],
                'Action "automatic_discovery" expects object in parameter "entity", array is given.'
            ]
        ];
    }

    /**
     * @param array $options
     * @param mixed $context
     *
     * @dataProvider executeOptionsDataProvider
     */
    public function testExecute(array $options, $context)
    {
        $matched = new \stdClass();
        $this->automaticDiscovery->expects($this->once())
            ->method('discoverSimilar')
            ->willReturn($matched);

        $this->action->initialize($options);
        $this->action->execute($context);

        $this->assertSame($matched, $this->contextAccessor->getValue($context, new PropertyPath('attribute')));
    }

    /**
     * @return array
     */
    public function executeOptionsDataProvider()
    {
        return [
            'valid entity' => [
                [
                    'entity' => new PropertyPath('entity'),
                    'attribute' => new PropertyPath('attribute')
                ],
                (object)[
                    'entity' => new \stdClass(), 'attribute' => null
                ],
                'Action "automatic_discovery" expects object in parameter "entity", NULL is given.'
            ]
        ];
    }
}
