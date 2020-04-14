<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor\OrderNotes;

use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\ChainProcessor;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\Context;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\ProcessorInterface;
use Oro\Component\Testing\Unit\EntityTrait;

class ChainProcessorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ChainProcessor
     */
    private $chainProcessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->chainProcessor = new ChainProcessor();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->chainProcessor);
    }

    public function testProcessNotes()
    {
        /**
         * @var $order Order
         */
        $order = $this->getEntity(Order::class, ['id' => 12]);
        /**
         * @var $orderNote1 OrderNote
         * @var $orderNote2 OrderNote
         */
        $orderNote1 = $this->getEntity(OrderNote::class, ['id' => 1]);
        $orderNote2 = $this->getEntity(OrderNote::class, ['id' => 2]);
        $order
            ->addOrderNote($orderNote1)
            ->addOrderNote($orderNote2);

        /**
         * @var $processorThatDoSkip ProcessorInterface
         * @var $processorSimple ProcessorInterface
         */
        $processorThatDoSkip = $this->createMock(ProcessorInterface::class);
        $processorSimple = $this->createMock(ProcessorInterface::class);
        $processorThatDoSkip
            ->method('process')
            ->willReturnCallback(
                function (Context $context) {
                    $orderNote = $context->getOrderNote();
                    if ($orderNote->getId() === 1) {
                        $context->markItemSkipped();
                    }
                    $orderNote->setMessage('Processed by processor that do skip of 1st Order Note.');
                }
            );
        $processorSimple
            ->method('process')
            ->willReturnCallback(
                function (Context $context) {
                    $orderNote = $context->getOrderNote();
                    $orderNote->setMessage(
                        sprintf(
                            '%s Processed by simple processor.',
                            $orderNote->getMessage()
                        )
                    );
                }
            );
        $this->chainProcessor->addProcessor($processorThatDoSkip);
        $this->chainProcessor->addProcessor($processorSimple);

        $this->chainProcessor->processNotes($order);

        $this->assertEquals('Processed by processor that do skip of 1st Order Note.', $orderNote1->getMessage());
        $this->assertEquals(
            'Processed by processor that do skip of 1st Order Note. Processed by simple processor.',
            $orderNote2->getMessage()
        );
    }

    public function testProcessWithoutProcessorWithoutErrors()
    {
        /**
         * @var $order Order
         */
        $order = $this->getEntity(Order::class, ['id' => 12]);

        $this->chainProcessor->processNotes($order);
    }
}
