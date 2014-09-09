<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;

class AbstractNormalizer implements SerializerAwareInterface
{
    /** @var SerializerInterface|NormalizerInterface|DenormalizerInterface */
    protected $serializer;

    /** @var ImportHelper */
    protected $importHelper;

    /**
     * @param ImportHelper $importHelper
     */
    public function __construct(ImportHelper $importHelper)
    {
        $this->importHelper = $importHelper;
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @throws \InvalidArgumentException
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface',
                    'Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
        $this->serializer = $serializer;
    }

    /**
     * Convert assoc array with 'sample_key' keys notation
     * to camel case 'sampleKey'
     *
     * @param array $data
     *
     * @return array
     */
    protected function convertToCamelCase($data)
    {
        $result = [];
        foreach ($data as $itemName => $item) {
            $fieldName = preg_replace_callback(
                '/_([a-z])/',
                function ($string) {
                    return strtoupper($string[1]);
                },
                $itemName
            );

            $result[$fieldName] = $item;
        }

        return $result;
    }

    /**
     * @param object $resultObject
     * @param array  $data
     */
    protected function fillResultObject($resultObject, $data)
    {
        $reflObj                  = new \ReflectionObject($resultObject);
        $importedEntityProperties = $reflObj->getProperties();

        /** @var \ReflectionProperty $reflectionProperty */
        foreach ($importedEntityProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $name = $reflectionProperty->getName();

            if (isset($data[$name]) && !is_null($data[$name])) {
                $reflectionProperty->setValue($resultObject, $data[$name]);
            }
        }
    }

    /**
     * @param array  $data
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    protected function denormalizeCreatedUpdated($data, $format, $context = [])
    {
        $dateTimeFormat    = ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'];
        $data['createdAt'] = $this->serializer->denormalize(
            $data['createdAt'],
            'DateTime',
            $format,
            array_merge($context, $dateTimeFormat)
        );
        $data['updatedAt'] = $this->serializer->denormalize(
            $data['updatedAt'],
            'DateTime',
            $format,
            array_merge($context, $dateTimeFormat)
        );

        return $data;
    }

    /**
     * @param array  $data
     * @param string $name
     * @param string $type
     * @param mixed  $format
     * @param array  $context
     *
     * @return null|object
     */
    protected function denormalizeObject(array $data, $name, $type, $format = null, $context = array())
    {
        $result = null;
        if (!empty($data[$name])) {
            $result = $this->serializer->denormalize($data[$name], $type, $format, $context);
        }

        return $result;
    }

    /**
     * @param array $context
     *
     * @return Integration
     * @throws \LogicException
     */
    protected function getIntegrationFromContext(array $context)
    {
        return $this->importHelper->getIntegrationFromContext($context);
    }
}
