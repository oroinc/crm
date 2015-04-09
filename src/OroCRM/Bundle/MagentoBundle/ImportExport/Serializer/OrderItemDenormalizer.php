<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderItemDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
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
     * @param object $resultObject
     * @param array  $data
     */
    protected function fillResultObject($resultObject, array $data)
    {
        $reflectionObject = new \ReflectionObject($resultObject);
        $importedEntityProperties = $reflectionObject->getProperties();

        /** @var \ReflectionProperty $reflectionProperty */
        foreach ($importedEntityProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $name = $reflectionProperty->getName();

            if (array_key_exists($name, $data) && !is_null($data[$name])) {
                $reflectionProperty->setValue($resultObject, $data[$name]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $type === MagentoConnectorInterface::ORDER_ITEM_TYPE;
    }
}
