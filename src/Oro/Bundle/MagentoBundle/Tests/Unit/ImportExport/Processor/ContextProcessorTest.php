<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\ContextProcessor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\SerializerInterface;

class ContextProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContextProcessor */
    protected $processor;

    protected function setUp(): void
    {
        $this->processor = new ContextProcessor();
    }

    public function testProcess()
    {
        $item = ['property' => 'value'];
        $expectedProperty = 'property2';
        $expectedValue = 'value2';

        /** @var \PHPUnit\Framework\MockObject\MockObject|SerializerInterface $serializer */
        $serializer = $this->createMock('Symfony\Component\Serializer\SerializerInterface');
        $serializer->expects($this->once())
            ->method('deserialize')
            ->will(
                $this->returnCallback(
                    function ($item) {
                        return (object)$item;
                    }
                )
            );

        $this->processor->setSerializer($serializer);

        /** @var \PHPUnit\Framework\MockObject\MockObject|StrategyInterface $strategy */
        $strategy = $this->createMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
        $strategy->expects($this->once())
            ->method('process')
            ->with($this->isType('object'))
            ->will(
                $this->returnCallback(
                    function ($item) use ($expectedProperty, $expectedValue) {
                        $item->{$expectedProperty} = $expectedValue;

                        return $item;
                    }
                )
            );

        $this->processor->setStrategy($strategy);

        $this->processor->setEntityName('\stdClass');

        /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface $context */
        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())->method('getConfiguration')->will($this->returnValue([]));

        $this->processor->setImportExportContext($context);

        $result = $this->processor->process($item);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertNotEmpty(
            $propertyAccessor->getValue($result, $expectedProperty),
            $expectedValue
        );
    }
}
