<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Region;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;

class RegionDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * For importing regions
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return bool|Region
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

            $combinedCode = BAPRegion::getRegionCombinedCode($data['countryCode'], $code);
            $resultObject->setCombinedCode($combinedCode);
            $resultObject->setCountryCode($data['countryCode']);
        }

        // magento can bring empty name, region will be skipped in strategy
        if (isset($data['name'])) {
            $resultObject->setName($data['name']);
        }

        return $resultObject;
    }

    /**
     * Used in import
     *
     * @param mixed  $data
     * @param string $type
     * @param null   $format
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == MagentoConnectorInterface::REGION_TYPE;
    }
}
