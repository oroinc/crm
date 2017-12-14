<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

/**
 * Validate OrderNotes and detach it from order and entity manager in case of invalid entity
 */
class ValidationNoteProcessor implements ProcessorInterface
{
    /**
     * @var ImportStrategyHelper
     */
    private $importStrategyHelper;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param ImportStrategyHelper $importStrategyHelper
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ImportStrategyHelper $importStrategyHelper, DoctrineHelper $doctrineHelper)
    {
        $this->importStrategyHelper = $importStrategyHelper;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param Context $context
     */
    public function process(Context $context)
    {
        $order = $context->getOrder();
        $orderNote = $context->getOrderNote();

        if (null === $this->importStrategyHelper->validateEntity($orderNote)) {
            return;
        }

        $order->removeOrderNote($orderNote);
        $this->doctrineHelper
            ->getEntityManager($orderNote)
            ->detach($orderNote);

        $context->markItemSkipped();
    }
}
