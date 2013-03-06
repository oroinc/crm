<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $data['type'] = !isset($data['type']) ?  ChoiceType::TYPE_CONTAINS : $data['type'];

        $operator = $this->getOperator((int) $data['type']);

        if (!$operator) {
            $operator = 'LIKE';
        }

        /** @var $proxyQuery ProxyQuery */
        $queryBuilder = $proxyQuery->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $field, $data['value'], $operator);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            ChoiceType::TYPE_CONTAINS     => 'LIKE',
            ChoiceType::TYPE_NOT_CONTAINS => 'NOT LIKE',
            ChoiceType::TYPE_EQUAL        => '=',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'format'   => '%%%s%%'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        // TODO : add rendering
        return array('oro_grid_type_filter_default', array(
            'label'      => $this->getLabel(),
            'field_type' => 'text',
            'field_options' => array('required' => false)
        ));
    }
}
