<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Doctrine\Common\Util\ClassUtils;

abstract class FlexibleSoapController extends SoapController
{
    /**
     * {@inheritDoc}
     */
    protected function fixRequestAttributes($entity, $attributeKey = 'attributes')
    {
        parent::fixRequestAttributes($entity);

        $request = $this->container->get('request');
        $data = $request->request->get($this->getForm()->getName());

        $values = array();
        if (isset($data[$attributeKey])) {
            foreach ($data[$attributeKey] as $attr) {
                $values[$attr->code] = $attr->value;
            }
            $data[$attributeKey] = $values;
        }

        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $entityClass = str_replace('Soap', '', $entityClass);
        $data = $this->container->get('oro_soap.request')->getFixedAttributesData($entityClass, $data, 'attributes', $attributeKey);

        $request->request->set($this->getForm()->getName(), $data);
    }
}
