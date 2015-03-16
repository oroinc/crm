<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class ContextProcessor extends ImportProcessor
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
