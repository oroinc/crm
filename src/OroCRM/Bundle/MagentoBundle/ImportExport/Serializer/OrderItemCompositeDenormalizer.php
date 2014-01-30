<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\OrderItemDataConverter;

class OrderItemCompositeDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /** @var OrderItemDataConverter */
    protected $dataConverter;

    public function __construct(EntityManager $em, OrderItemDataConverter $dataConverter)
    {
        parent::__construct($em);
        $this->dataConverter = $dataConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = $this->dataConverter->convertToImportFormat($data);

        /** @var OrderItem $object */
        $className = MagentoConnectorInterface::ORDER_ITEM_TYPE;
        $object    = new $className();
        $this->fillResultObject($object, $data);
        if ($object->getDiscountPercent()) {
            $object->setDiscountPercent($object->getDiscountPercent() / 100);
        }
        if ($object->getTaxPercent()) {
            $object->setTaxPercent($object->getTaxPercent() / 100);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == MagentoConnectorInterface::ORDER_ITEM_TYPE;
    }
}
