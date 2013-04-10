<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Doctrine\Common\Persistence\ObjectRepository;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

class FlexibleOptionsFilter extends AbstractFlexibleFilter
{
    /**
     * @var array
     */
    protected $valueOptions;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$this->isDataValid($data)) {
            return;
        }

        if (!is_array($data['value'])) {
            $data['value'] = array($data['value']);
        }

        foreach ($data['value'] as $key => $value) {
            $data['value'][$key] = trim($value);
            if (strlen($data['value'][$key]) == 0) {
                unset($data['value'][$key]);
            }
        }

        if (empty($data['value'])) {
            return;
        }

        // process type and operator
        $type = isset($data['type']) ? $data['type'] : false;
        $operator = $this->getOperator($type, ChoiceType::TYPE_CONTAINS);

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $data['value'], $operator);
    }

    /**
     * Checks if $data is valid
     *
     * @param mixed $data
     * @return bool
     */
    protected function isDataValid($data)
    {
        return is_array($data) && array_key_exists('value', $data) && !is_null($data['value']);
    }

    /**
     * Get operator as string
     *
     * @param string $type
     * @param int|null $default
     * @return int|bool
     */
    public function getOperator($type, $default = null)
    {
        $type = (int) $type;

        $choices = array(
            ChoiceType::TYPE_CONTAINS     => 'IN',
            ChoiceType::TYPE_NOT_CONTAINS => 'NOT IN',
            ChoiceType::TYPE_EQUAL        => '=',
        );

        if (isset($choices[$type])) {
            return $choices[$type];
        }

        if (!is_null($default) && isset($choices[$default])) {
            return $choices[$default];
        }

        return false;
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
