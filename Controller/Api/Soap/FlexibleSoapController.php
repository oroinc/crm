<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Doctrine\Common\Util\ClassUtils;

abstract class FlexibleSoapController extends SoapController
{
    /**
     * {@inheritDoc}
     */
    protected function fixRequestAttributes($entity, $attributeKey = 'values', $requestAttributeKey = 'attributes')
    {
        parent::fixRequestAttributes($entity);

        $request = $this->container->get('request');
        $data = $request->request->get($this->getForm()->getName());

        // fix attributes array format to make it associative
        // and compatible with SoapBundle\Entity\FlexibleAttribute
        $values = array();
        if (isset($data[$requestAttributeKey])) {
            foreach ($data[$requestAttributeKey] as $attr) {
                $values[$attr->code] = $attr->value;
            }
            $data[$requestAttributeKey] = $values;
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $entityClass = str_replace('Soap', '', $entityClass);

        $data = $this->container->get('oro_soap.request')->getFixedAttributesData($entityClass, $data, $attributeKey, $requestAttributeKey);
        $request->request->set($this->getForm()->getName(), $data);
    }
}
