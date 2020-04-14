<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor\OrderNotes;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\Context;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\ValidationNoteProcessor;
use Oro\Component\Testing\Unit\EntityTrait;

class ValidationNoteProcessorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var ValidationNoteProcessor
     */
    private $processor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | ImportStrategyHelper
     */
    private $importStrategyHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->importStrategyHelper = $this->createMock(ImportStrategyHelper::class);

        $this->processor = new ValidationNoteProcessor($this->importStrategyHelper, $this->doctrineHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->doctrineHelper);
        unset($this->importStrategyHelper);
        unset($this->processor);
    }

    /**
     * @dataProvider testProcessOrderNoteProvider
     *
     * @param $validationResult
     */
    public function testProcessOrderNote($validationResult)
    {
        /**
         * @var $order Order
         */
        $order = $this->getEntity(Order::class, ['id' => 12]);
        /**
         * @var $orderNote OrderNote
         */
        $orderNote = $this->getEntity(OrderNote::class, ['id' => 14]);
        $order->addOrderNote($orderNote);
        $context = Context::createContext($order, $orderNote);

        $this->importStrategyHelper
            ->expects($this->once())
            ->method('validateEntity')
            ->with($orderNote)
            ->willReturn($validationResult);

        if (null !== $validationResult) {
            $entityManager = $this->createMock(EntityManager::class);
            $this->doctrineHelper
                ->expects($this->once())
                ->method('getEntityManager')
                ->with($orderNote)
                ->willReturn($entityManager);

            $entityManager
                ->expects($this->once())
                ->method('detach')
                ->with($orderNote)
                ->willReturnSelf();
        }

        $this->processor->process($context);

        if (null === $validationResult) {
            $this->assertTrue($order->hasOrderNote($orderNote));
            $this->assertFalse($context->isItemSkipped());
        } else {
            $this->assertFalse($order->hasOrderNote($orderNote));
            $this->assertTrue($context->isItemSkipped());
        }
    }

    public function testProcessOrderNoteProvider()
    {
        return [
            'Test process valid order note' => [
                'validationResult' => null
            ],
            'Test process invalid order note' => [
                'validationResult' => ['Field "Message" must be non-empty !']
            ],
        ];
    }
}
