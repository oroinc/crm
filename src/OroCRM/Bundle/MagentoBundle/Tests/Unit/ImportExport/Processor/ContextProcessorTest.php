<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\ContextProcessor;

class ContextProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContextProcessor */
    protected $processor;

    protected function setUp()
    {
        $this->processor = new ContextProcessor();
    }

    public function testProcess()
    {
        $item = ['property' => 'value'];
        $expectedProperty = 'property2';
        $expectedValue = 'value2';

        /** @var \PHPUnit_Framework_MockObject_MockObject|SerializerInterface $serializer */
        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
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

        /** @var \PHPUnit_Framework_MockObject_MockObject|StrategyInterface $strategy */
        $strategy = $this->getMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
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

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
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
