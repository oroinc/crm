<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $value)
    {
        if (!$value || !is_array($value) || !array_key_exists('value', $value) || null === $value['value']) {
            return;
        }

        $value['value'] = trim($value['value']);

        if (strlen($value['value']) == 0) {
            return;
        }

        /** @var $proxyQuery ProxyQuery */
        $queryBuilder = $proxyQuery->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applyFilterByAttribute($queryBuilder, $alias, $field, $value['value']);
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
        return array('oro_grid_type_filter_default', array(
            'label' => $this->getLabel()
        ));
    }
}
