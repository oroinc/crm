<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Proxy\Proxy;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

abstract class FlexibleRestController extends RestController
{
    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity)
    {
        $result = parent::getPreparedItem($entity);
        if (array_key_exists('values', $result)) {
            $result['attributes'] = $result['values'];
            unset($result['values']);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function transformEntityField($field, &$value)
    {
        if ($field == 'values') {
            $flexibleValues = $value;
            $value = array();
            /** @var FlexibleValueInterface $flexibleValue */
            foreach ($flexibleValues as $flexibleValue) {
                if ($flexibleValue instanceof Proxy) {
                    /** @var Proxy $flexibleValue */
                    $flexibleValue->__load();
                }
                $attributeValue = $flexibleValue->getData();
                if ($attributeValue) {
                    /** @var Attribute $attribute */
                    $attribute = $flexibleValue->getAttribute();
                    parent::transformEntityField($attribute->getCode(), $attributeValue);
                    $attributeData = array('value' => $attributeValue);
                    if ($attributeValue instanceof TranslatableInterface) {
                        /** @var TranslatableInterface $flexibleValue */
                        $attributeData['locale'] = $flexibleValue->getLocale();
                    }
                    if ($attributeValue instanceof ScopableInterface) {
                        /** @var ScopableInterface $flexibleValue */
                        $attributeData['scope'] = $flexibleValue->getScope();
                    }
                    $value[$attribute->getCode()] = (object)$attributeData;
                }
            }
        } else {
            parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function fixRequestAttributes($entity)
    {
        parent::fixRequestAttributes($entity);

        $request = $this->getRequest()->request;
        $requestVariable = $this->getForm()->getName();
        $data = $request->get($requestVariable, array());

        /** @var ObjectRepository $attrRepository */
        $attrRepository = $this->getManager()
            ->getFlexibleManager()
            ->getAttributeRepository();
        $entityClass = ClassUtils::getRealClass(get_class($entity));
        $attrDef = $attrRepository->findBy(array('entityType' => $entityClass));
        $attrVal = isset($data['attributes']) ? $data['attributes'] : array();

        unset($data['attributes']);
        $data['values'] = array();

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

            $data['values'][$i] = array();
            $data['values'][$i]['id'] = $attr->getId();
            $data['values'][$i][$type] = $default;

            foreach ($attrVal as $fieldCode => $fieldValue) {
                if ($attr->getCode() == (string)$fieldCode) {
                    if (is_array($fieldValue)) {
                        if (array_key_exists('scope', $fieldValue)) {
                            $data['values'][$i]['scope'] = $fieldValue['scope'];
                        }
                        if (array_key_exists('locale', $fieldValue)) {
                            $data['values'][$i]['locale'] = $fieldValue['locale'];
                        }
                        $fieldValue = $fieldValue['value'];
                    }
                    $data['values'][$i][$type] = (string)$fieldValue;

                    break;
                }
            }
        }

        $request->set($requestVariable, $data);
    }
}
