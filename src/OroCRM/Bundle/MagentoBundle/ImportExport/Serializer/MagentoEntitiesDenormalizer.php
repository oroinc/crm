<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\ImportExportBundle\Field\FieldHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

/**
 * @todo: should be deleted during BAP-9349 implementation
 */
class MagentoEntitiesDenormalizer extends ConfigurableEntityNormalizer implements DenormalizerInterface
{
    /** @var RegistryInterface  */
    protected $doctrine;

    /**
     * MagentoEntitiesDenormalizer constructor.
     *
     * @param FieldHelper       $fieldHelper
     * @param RegistryInterface $doctrine
     */
    public function __construct(FieldHelper $fieldHelper, RegistryInterface $doctrine)
    {
        parent::__construct($fieldHelper);
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var OrderItem $object */
        $object = parent::denormalize($data, $class, $format, $context);

        $object->setChannel(
            $this->doctrine->getRepository('OroIntegrationBundle:Channel')->findOneBy($data['channel'])
        );

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        $usedTraits = class_uses($type);

        return in_array('OroCRM\Bundle\MagentoBundle\Entity\IntegrationEntityTrait', $usedTraits)
            && array_key_exists('channel', $data)
            && !array_key_exists('id', $data['channel'])
            && $type !== MagentoConnectorInterface::ORDER_ITEM_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return false;
    }
}
