<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Doctrine\Common\Persistence\ObjectRepository;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;

class FlexibleOptionsFilter extends AbstractFlexibleFilter
{
    /**
     * @var array
     */
    protected $valueOptions;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $value)
    {
        if (!$value || !is_array($value) || !array_key_exists('value', $value) || null === $value['value']) {
            return;
        }

        if (!is_array($value['value'])) {
            $value['value'] = array($value['value']);
        }

        foreach ($value['value'] as $key => $data) {
            $value['value'][$key] = trim($data);
            if (strlen($value['value'][$key]) == 0) {
                unset($value['value'][$key]);
            }
        }

        if (empty($value['value'])) {
            return;
        }

        // process type and operator
        if (!isset($data['type'])) {
            if (is_array($value)) {
                $operator = $this->getOperator(ChoiceType::TYPE_CONTAINS);
            } else {
                $operator = $this->getOperator(ChoiceType::TYPE_EQUAL);
            }
        } else {
            $operator = $this->getOperator((int) $data['type']);
        }

        /** @var $proxyQuery ProxyQuery */
        $queryBuilder = $proxyQuery->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $field, $value['value'], $operator);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            ChoiceType::TYPE_CONTAINS         => 'IN',
            ChoiceType::TYPE_NOT_CONTAINS     => 'NOT IN',
            ChoiceType::TYPE_EQUAL            => '=',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_flexible_options', array(
            'label'         => $this->getLabel(),
            'field_options' => array(
                'choices'  => $this->getValueOptions(),
                'multiple' => $this->getOption('multiple') ? true : false
            ),
        ));
    }

    /**
     * @return array
     * @throws \LogicException
     */
    public function getValueOptions()
    {
        if (null === $this->valueOptions) {
            $filedName = $this->getOption('field_name');

            /** @var $attributeRepository ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            /** @var $attribute Attribute */
            $attribute = $attributeRepository->findOneBy(
                array('entityType' => $this->flexibleManager->getFlexibleName(), 'code' => $filedName)
            );
            if (!$attribute) {
                throw new \LogicException('There is no flexible attribute with name ' . $filedName . '.');
            }

            /** @var $optionsRepository ObjectRepository */
            $optionsRepository = $this->flexibleManager->getAttributeOptionRepository();
            $options = $optionsRepository->findBy(
                array('attribute' => $attribute)
            );

            $this->valueOptions = array();
            /** @var $option AttributeOption */
            foreach ($options as $option) {
                $optionValue = $option->getOptionValue();
                if ($optionValue) {
                    $this->valueOptions[$option->getId()] = $optionValue->getValue();
                }
            }
        }

        return $this->valueOptions;
    }
}
