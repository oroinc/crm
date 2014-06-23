<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartAddressNormalizer extends ConfigurableEntityNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var CartAddress $result */
        $result = parent::denormalize($data, $class, $format, $context);
        if (!empty($data['address_id'])) {
            $result->setOriginId($data['address_id']);
        }
        if (!$result->getCountry()) {
            return null;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return MagentoConnectorInterface::CART_ADDRESS_TYPE == $type;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof CartAddress;
    }
}
