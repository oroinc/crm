<?php

namespace Oro\Bundle\SoapBundle\Entity;

// TODO: Remove Collection and ContainerAware uses after BAP-721 implementation
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

class RequestFix extends ContainerAware
{
    /**
     * @var FlexibleManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param FlexibleManagerRegistry $managerRegistry
     */
    public function __construct(FlexibleManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @todo Remove this code after BAP-721 implementation
     * @deprecated This method will be removed after all code will be refactored to use getFixedData()
     * @param string $name
     */
    public function fix($name)
    {
        $request = $this->container->get('request');
        $entity = $request->get($name);
        if (!is_object($entity)) {
            return;
        }

        $data = array();
        foreach ((array)$entity as $field => $value) {
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

        if ($entity instanceof FlexibleInterface) {
            $data = $this->getFixedAttributesData($entityClass, $data, 'attributes');
        }

        $request->request->set($name, $data);
    }

    /**
     * Fix Request object so forms can be handled correctly
     *
     * @param string $entityClass
     * @param array $data
     * @param string $attributeKey
     * @param string $requestAttributeKey
     * @return array
     */
    public function getFixedAttributesData($entityClass, array $data, $attributeKey = 'values', $requestAttributeKey = 'attributes')
    {
        /** @var ObjectRepository $attrRepository */
        $attrRepository = $this->managerRegistry
            ->getManager($entityClass)
            ->getAttributeRepository();
        $attrDef = $attrRepository->findBy(array('entityType' => $entityClass));
        $attrVal = isset($data[$requestAttributeKey]) ? $data[$requestAttributeKey] : array();

        unset($data[$requestAttributeKey]);
        $data[$attributeKey] = array();

        foreach ($attrDef as $i => $attr) {
            /* @var AbstractEntityAttribute $attr */
            if ($attr->getBackendType() == 'options') {
                if (in_array(
                    $attr->getAttributeType(),
                    array(
                        'oro_flexibleentity_multiselect',
                        'oro_flexibleentity_multicheckbox',
                    )
                )) {
                    $type = 'options';
                    $default = array($attr->getOptions()->offsetGet(0)->getId());
                } else {
                    $type = 'option';
                    $default = $attr->getOptions()->offsetGet(0)->getId();
                }
            } else {
                $type = $attr->getBackendType();
                $default = null;
            }

            $data[$attributeKey][$i] = array();
            $data[$attributeKey][$i]['id'] = $attr->getId();
            $data[$attributeKey][$i][$type] = $default;

            foreach ($attrVal as $fieldCode => $fieldValue) {
                if ($attr->getCode() == (string)$fieldCode) {
                    if (is_array($fieldValue)) {
                        if (array_key_exists('scope', $fieldValue)) {
                            $data[$attributeKey][$i]['scope'] = $fieldValue['scope'];
                        }
                        if (array_key_exists('locale', $fieldValue)) {
                            $data[$attributeKey][$i]['locale'] = $fieldValue['locale'];
                        }
                        $fieldValue = $fieldValue['value'];
                    }
                    $data[$attributeKey][$i][$type] = (string)$fieldValue;

                    break;
                }
            }
        }

        return $data;
    }
}
