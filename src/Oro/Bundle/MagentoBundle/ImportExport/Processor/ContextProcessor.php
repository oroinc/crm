<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor;

use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareImportProcessor;

/**
 * Processes deserialized item by certain strategy
 */
class ContextProcessor extends StepExecutionAwareImportProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!$this->serializer || !$this->strategy) {
            throw new \InvalidArgumentException('Processor was not configured properly');
        }

        $object = $this->serializer->deserialize(
            $item,
            $this->getEntityName(),
            '',
            $this->context->getConfiguration()
        );

        return $this->strategy->process($object);
    }
}
