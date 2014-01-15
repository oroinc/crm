<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

class AbstractNormalizer implements SerializerAwareInterface
{
    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    /** @var  ChannelRepository */
    protected $channelRepository;

    public function __construct(EntityManager $em)
    {
        $this->channelRepository = $em->getRepository('OroIntegrationBundle:Channel');
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
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
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
     * Format payment info
     * Magento brings only CC payments info,
     * for different types information could not be taken from order info
     *
     * @param array $paymentDetails
     *
     * @return string
     */
    public function denormalizePaymentDetails($paymentDetails)
    {
        $paymentDetails['cc_type']  = isset($paymentDetails['cc_type']) ? trim($paymentDetails['cc_type']) : null;
        $paymentDetails['cc_last4'] = isset($paymentDetails['cc_last4']) ? trim($paymentDetails['cc_last4']) : null;

        if (!empty($paymentDetails['cc_type']) && !empty($paymentDetails['cc_last4'])) {
            $paymentDetails = sprintf(
                "Card [%s, %s]",
                $paymentDetails['cc_type'],
                $paymentDetails['cc_last4']
            );
        } else {
            $paymentDetails = null;
        }

        return $paymentDetails;
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
     * @return \Oro\Bundle\IntegrationBundle\Entity\Channel
     * @throws \LogicException
     */
    protected function getChannelFromContext(array $context)
    {
        if (!isset($context['channel'])) {
            throw new \LogicException('Context should contain reference to channel');
        }

        return $this->channelRepository->getOrLoadById($context['channel']);
    }
}
