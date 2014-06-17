<?php

namespace OroCRM\Bundle\MagentoBundle\Service;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImportHelper
{
    /**
     * @var ChannelRepository
     */
    protected $channelRepository;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->channelRepository = $em->getRepository('OroIntegrationBundle:Channel');
    }

    /**
     * @param array $context
     *
     * @return \Oro\Bundle\IntegrationBundle\Entity\Channel
     * @throws \LogicException
     */
    public function getChannelFromContext(array $context)
    {
        if (!isset($context['channel'])) {
            throw new \LogicException('Context should contain reference to channel');
        }

        return $this->channelRepository->getOrLoadById($context['channel']);
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
     * @param array $address
     * @return array
     */
    public function getFixedAddress(array $address)
    {
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        if (!empty($address['country_id'])) {
            $propertyAccess->setValue($address, '[country][iso2Code]', $address['country_id']);
        }
        if (array_key_exists('region', $address)) {
            $address['regionText'] = $address['region'];
            unset($address['region']);
        }
        if (!empty($address['region_id'])) {
            $propertyAccess->setValue($address, '[region][combinedCode]', $address['region_id']);
        }
        unset($address['country_id']);
        unset($address['region_id']);

        return $address;
    }
}
