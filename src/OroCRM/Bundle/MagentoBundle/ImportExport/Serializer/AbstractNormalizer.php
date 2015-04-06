<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

class AbstractNormalizer
{
    /**
     * @param object $resultObject
     * @param array  $data
     */
    protected function fillResultObject($resultObject, $data)
    {
        $reflObj                  = new \ReflectionObject($resultObject);
        $importedEntityProperties = $reflObj->getProperties();

        /** @var \ReflectionProperty $reflectionProperty */
        foreach ($importedEntityProperties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $name = $reflectionProperty->getName();

            if (isset($data[$name]) && !is_null($data[$name])) {
                $reflectionProperty->setValue($resultObject, $data[$name]);
            }
        }
    }
}
