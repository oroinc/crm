<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Leads;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;

class ByDateManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();

        $field->setName('createdDate');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => $this->translate('orocrm.report.datagrid.columns.created_date'),
                'expression'  => 'createdDate',
                'filter_type' => FilterInterface::TYPE_DATE,
                'required'    => false,
                'sortable'    => true,
                'show_column' => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('leadsCount');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('orocrm.report.datagrid.columns.leads_count'),
                'expression'  => 'leadsCount',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'show_column' => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );

        $fieldsCollection->add($field);
    }
}
