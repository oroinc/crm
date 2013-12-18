<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\CartItemDataConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\CartItem;

class CartItemNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ENTITIES_TYPE = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\CartItem>';
    const ENTITY_TYPE   = 'OroCRM\Bundle\MagentoBundle\Entity\CartItem';

    /** @var CartItemDataConverter */
    protected $itemConverter;

    public function __construct(
        EntityManager $em,
        CartItemDataConverter $itemConverter
    ) {
        parent::__construct($em);
        $this->itemConverter = $itemConverter;
    }


    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == self::ENTITY_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = is_array($data) ? $data : [];
        $data = $this->itemConverter->convertToImportFormat($data);

        $data = $this->denormalizeCreatedUpdated($data, $format, $context);

        $cartItem = new CartItem();
        $this->fillResultObject($cartItem, $data);

        return $cartItem;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CartItem;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'id'        => $object->getId(),
                'quote_id'  => $object->getCart() ? $object->getCart()->getId() : null,
                'origin_id' => $object->getOriginId(),
                'price'     => $object->getPrice(),
                'sku'       => $object->getSku(),
            );
        }

        return $result;
    }
}
