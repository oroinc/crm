<?php

namespace Oro\Bundle\AccountBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class AccountsViewList extends AbstractViewsList
{
    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        return [
            (new View(
                'oro_account.duplicities',
                ['duplicate' => ['value' => BooleanFilterType::TYPE_YES]],
                ['name' => 'ASC']
            ))
            ->setLabel($this->translator->trans(
                'oro.datagrid.gridview.duplicate.label',
                ['%entity%' => $this->translator->trans('oro.account.entity_plural_label')]
            ))
        ];
    }
}
