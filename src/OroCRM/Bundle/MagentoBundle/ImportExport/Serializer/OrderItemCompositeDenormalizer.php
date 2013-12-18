<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\OrderItemDataConverter;

class OrderItemCompositeDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const TYPE            = 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem';
    const COLLECTION_TYPE = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\OrderItem>';

    /** @var OrderItemDataConverter */
    protected $converter;

    public function __construct(EntityManager $em, OrderItemDataConverter $converter)
    {
        parent::__construct($em);
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = $this->converter->convertToImportFormat($data);

        $className = self::TYPE;
        /** @var OrderItem $object */
        $object = new $className();
        $this->fillResultObject($object, $data);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == self::TYPE;
    }
}
