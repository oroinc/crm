<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

class PaymentDetailsNormalizer extends ConfigurableEntityNormalizer implements DenormalizerInterface
{
    /**
     * @var string
     */
    protected $supportedClass;

    /**
     * @param string $supportedClass
     */
    public function setSupportedClass($supportedClass)
    {
        $this->supportedClass = $supportedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['paymentDetails'])) {
            $data['paymentDetails'] = $this->denormalizePaymentDetails($data['paymentDetails']);
        }

        return parent::denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $type === $this->supportedClass;
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
        $ccType = isset($paymentDetails['cc_type']) ? trim($paymentDetails['cc_type']) : null;
        $ccLast4 = isset($paymentDetails['cc_last4']) ? trim($paymentDetails['cc_last4']) : null;

        $paymentDetailsString = null;

        if (!empty($paymentDetails['cc_type']) && !empty($paymentDetails['cc_last4'])) {
            $paymentDetailsString = sprintf('Card [%s, %s]', $ccType, $ccLast4);
        }

        return $paymentDetailsString;
    }
}
