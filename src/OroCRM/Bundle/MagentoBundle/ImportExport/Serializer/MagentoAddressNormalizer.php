<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Address as MagentoAddress;

class MagentoAddressNormalizer extends TypedAddressNormalizer
{
    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * @param AddressNormalizer $addressNormalizer
     */
    public function __construct(AddressNormalizer $addressNormalizer)
    {
        parent::__construct($addressNormalizer);
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = parent::denormalize($data, $class, $format, $context);

        // can be empty when using this normalizer with cart
        if (!empty($data['customerAddressId'])) {
            $result->setOriginId($data['customerAddressId']);
        }

        foreach (['created', 'updated'] as $dateField) {
            if (!empty($data[$dateField])) {
                $this->accessor->setValue(
                    $result,
                    $dateField,
                    $this->serializer->denormalize(
                        $data[$dateField],
                        'DateTime',
                        null,
                        ['type' => 'datetime', 'format' => 'Y-m-d H:i:s']
                    )
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return MagentoConnectorInterface::CUSTOMER_ADDRESS_TYPE == $type;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MagentoAddress;
    }
}
