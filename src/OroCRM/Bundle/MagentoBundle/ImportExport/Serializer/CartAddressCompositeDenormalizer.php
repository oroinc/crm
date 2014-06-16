<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartAddressCompositeDenormalizer extends OrderAddressCompositeDenormalizer
{
    /** @var array */
    protected $additionalProperties = ['originId'];

    /** @var AddressDataConverter */
    protected $dataConverter;

    public function __construct(AddressNormalizer $addressNormalizer, AddressDataConverter $dataConverter)
    {
        TypedAddressNormalizer::__construct($addressNormalizer);
        $this->dataConverter = $dataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = parent::denormalize($data, $class, $format, $context);
        if (!$result->getCountry()) {
            return null;
        }

        if (isset($data['created_at'], $data['updated_at'])) {
            $updated = $this->serializer->denormalize(
                $data['updated_at'],
                'DateTime',
                null,
                ['type' => 'datetime', 'format' => 'Y-m-d H:i:s']
            );
            $created = $this->serializer->denormalize(
                $data['created_at'],
                'DateTime',
                null,
                ['type' => 'datetime', 'format' => 'Y-m-d H:i:s']
            );

            $result->setCreated($created);
            $result->setUpdated($updated);
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
