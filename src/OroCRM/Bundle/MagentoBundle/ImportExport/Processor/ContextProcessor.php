<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareImportProcessor;

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
            null,
            $this->context->getConfiguration()
        );

        return $this->strategy->process($object);
    }
}
