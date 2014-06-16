<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Region;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class RegionDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (empty($data)) {
            return false;
        }

        /** @var Region $resultObject */
        $className    = MagentoConnectorInterface::REGION_TYPE;
        $resultObject = new $className();

        if (isset($data['region_id'])) {
            $resultObject->setRegionId($data['region_id']);
        }

        if (isset($data['code'])) {
            $code = $data['code'];
            $resultObject->setCode($code);

            // Some magento region codes are already combined
            $countryCode = $data['countryCode'];
            if (strpos($code, $countryCode . BAPRegion::SEPARATOR) === 0) {
                $combinedCode = $code;
            } else {
                $combinedCode = BAPRegion::getRegionCombinedCode($countryCode, $code);
            }
            $resultObject->setCombinedCode($combinedCode);
            $resultObject->setCountryCode($countryCode);
        }

        // magento can bring empty name, region will be skipped in strategy
        if (isset($data['name'])) {
            $resultObject->setName($data['name']);
        }

        return $resultObject;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return $type == MagentoConnectorInterface::REGION_TYPE;
    }
}
