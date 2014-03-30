<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter;

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

            $result->setCreatedAt($created);
            $result->setUpdatedAt($updated);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            is_array($data)
            && class_exists($type)
            && MagentoConnectorInterface::CART_ADDRESS_TYPE == $type;
    }
}
