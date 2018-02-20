<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Psr\Log\LoggerInterface;

class DefaultConfigurableEntityDenormalizer implements DenormalizerInterface
{
    /** @var FieldHelper */
    protected $fieldHelper;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param FieldHelper     $fieldHelper
     * @param LoggerInterface $logger
     */
    public function __construct(FieldHelper $fieldHelper, LoggerInterface $logger)
    {
        $this->fieldHelper = $fieldHelper;
        $this->logger      = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $this->logger->warning(
            sprintf('Invalid configuration for %s for mapping configurable entity attributes.', $class),
            [
                'data'    => $data,
                'context' => $context
            ]
        );

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            !is_array($data) &&
            class_exists($type) &&
            $this->fieldHelper->hasConfig($type) &&
            !empty($context['channelType']) &&
            $context['channelType'] == MagentoChannelType::TYPE;
    }
}
