<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\OrderAddressDataConverter;

class OrderAddressCompositeDenormalizer extends TypedAddressNormalizer
{
    const TYPE = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\OrderAddress';

    /** @var array */
    protected $additionalProperties = ['fax', 'phone'];

    /** @var OrderAddressDataConverter */
    protected $converter;

    public function __construct(AddressNormalizer $addressNormalizer, OrderAddressDataConverter $converter)
    {
        parent::__construct($addressNormalizer);
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = $this->converter->convertToImportFormat($data);
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
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            is_array($data)
            && class_exists($type)
            && static::TYPE == $type;
    }
}
