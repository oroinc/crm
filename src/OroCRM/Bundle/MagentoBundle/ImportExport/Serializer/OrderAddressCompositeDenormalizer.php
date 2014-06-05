<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\OrderAddressDataConverter;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderAddressCompositeDenormalizer extends TypedAddressNormalizer
{
    /** @var array */
    protected $additionalProperties = ['fax', 'phone'];

    /** @var OrderAddressDataConverter */
    protected $dataConverter;

    public function __construct(AddressNormalizer $addressNormalizer, OrderAddressDataConverter $dataConverter)
    {
        parent::__construct($addressNormalizer);
        $this->dataConverter = $dataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data             = $this->dataConverter->convertToImportFormat($data);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $result = parent::denormalize($data, $class, $format, $context);
        foreach ($this->additionalProperties as $property) {
            if (!empty($data[$property])) {
                $propertyAccessor->setValue($result, $property, $data[$property]);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return MagentoConnectorInterface::ORDER_ADDRESS_TYPE === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof OrderAddress;
    }
}
