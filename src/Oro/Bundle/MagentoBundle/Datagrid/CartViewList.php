<?php

namespace Oro\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberRangeFilterType;

class CartViewList extends AbstractViewsList
{
    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        return [
            (new View(
                'non_empty',
                [
                    'itemsQty' => [
                        'type' => NumberRangeFilterType::TYPE_GREATER_THAN,
                        'value' => 0,
                    ],
                ]
            ))
            ->setLabel($this->translator->trans('oro.magento.datagrid.views.non_empty_carts.label'))
            ->setDefault(true)
        ];
    }
}
