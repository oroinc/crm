<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class ContextOrderProcessor extends ImportProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $object = $this->serializer->deserialize(
            $item,
            $this->getEntityName(),
            null,
            $this->context->getConfiguration()
        );

        return $this->strategy->process($object);
    }
}
