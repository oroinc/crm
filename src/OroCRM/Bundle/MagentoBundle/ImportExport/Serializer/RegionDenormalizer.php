<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Region;

class RegionDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\Region';

    /**
     * For importing regions
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return object|Region
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (empty($data)) {
            return false;
        }

        $className    = self::TYPE;
        $resultObject = new $className();

        if (isset($data['region_id'])) {
            $resultObject->setRegionId($data['region_id']);
        }

        if (isset($data['code'])) {
            $code = $data['code'];
            $resultObject->setCode($code);

            $combinedCode = $data['countryCode'] . '.' . $code;
            $resultObject->setCombinedCode($combinedCode);
            $resultObject->setCountryCode($data['countryCode']);
        }

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
        return is_array($data) && $type == self::TYPE;
    }
}
