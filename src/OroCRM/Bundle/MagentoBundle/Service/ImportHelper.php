<?php

namespace OroCRM\Bundle\MagentoBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class ImportHelper
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param array $context
     *
     * @return Integration
     * @throws \LogicException
     */
    public function getIntegrationFromContext(array $context)
    {
        if (!isset($context['channel'])) {
            throw new \LogicException('Context should contain reference to channel');
        }

        return $this->registry
            ->getRepository('OroIntegrationBundle:Channel')
            ->getOrLoadById($context['channel']);
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
}
