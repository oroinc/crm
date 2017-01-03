<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class ContactsViewList extends AbstractViewsList
{
    /**
     * {@inheritdoc}
     */
    protected function getViewsList()
    {
        return [
            (new View(
                'oro_contact.duplicities',
                ['duplicate' => ['value' => BooleanFilterType::TYPE_YES]],
                ['email' => 'ASC']
            ))
            ->setLabel($this->translator->trans(
                'oro.datagrid.gridview.duplicate.label',
                ['%entity%' => $this->translator->trans('orocrm.contact.entity_label')]
            ))
        ];
    }
}
