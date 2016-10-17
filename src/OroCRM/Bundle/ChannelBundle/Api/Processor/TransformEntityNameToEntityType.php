<?php

namespace OroCRM\Bundle\ChannelBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;

class TransformEntityNameToEntityType implements ProcessorInterface
{
    /** @var ValueNormalizer */
    protected $valueNormalizer;

    /**
     * @param ValueNormalizer $valueNormalizer
     */
    public function __construct(ValueNormalizer $valueNormalizer)
    {
        $this->valueNormalizer = $valueNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeLoadedDataContext $context */

        $data = $context->getResult();
        if (!is_array($data)) {
            return;
        }

        if (!isset($data['entities']) || empty($data['entities']) || !is_array($data['entities'])) {
            return;
        }

        $data['entities'] = array_map(
            function ($entity) use ($context) {
                if (isset($entity['name'])) {
                    $entity['name'] = ValueNormalizerUtil::convertToEntityType(
                        $this->valueNormalizer,
                        $entity['name'],
                        $context->getRequestType()
                    );
                }

                return $entity;
            },
            $data['entities']
        );

        $context->setResult($data);
    }
}
