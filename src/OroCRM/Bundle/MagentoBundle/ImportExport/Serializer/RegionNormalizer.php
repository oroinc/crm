<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Region;

class RegionNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const STORE_TYPE     = 'OroCRM\Bundle\MagentoBundle\Entity\Region';

    /**
     * For exporting regions
     *
     * @param Region $object
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'name'         => $object->getName(),
                'code'         => $object->getCode(),
                'combinedCode' => $object->getCombinedCode(),
            );
        }

        return $result;
    }

    /**
     * For importing regions
     *
     * @param mixed $data
     * @param string $class
     * @param null $format
     * @param array $context
     * @return object|Region
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = is_array($data) ? $data : [];

        if (empty($data)) {
            return false;
        }

        $resultObject = new Region();

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
     * Used in export
     *
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Region;
    }

    /**
     * Used in import
     *
     * @param mixed $data
     * @param string $type
     * @param null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Region';
    }
}
