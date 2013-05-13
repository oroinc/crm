<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;

abstract class FlexibleSoapController extends SoapController
{
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);
        return parent::processForm($entity);
    }

    protected function fixRequestAttributes($entity)
    {
        $request = $this->container->get('request');
        $entityData = $request->get($this->getForm()->getName());
        if (!is_object($entityData)) {
            return;
        }

        $data = array();
        foreach ((array)$entityData as $field => $value) {
            // special case for ordered arrays
            if ($value instanceof \stdClass && isset($value->item) && is_array($value->item)) {
                $value = (array) $value->item;
            }

            if ($value instanceof Collection) {
                $value = $value->toArray();
            }

            if (!is_null($value)) {
                $data[preg_replace('/[^\w+]+/i', '', $field)] = $value;
            }
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $entityClass = str_replace('Soap', '', $entityClass);

        $data = $this->container->get('oro_soap.request')->getFixedData($entityClass, $data);

        $request->request->set($this->getForm()->getName(), $data);
    }
}
