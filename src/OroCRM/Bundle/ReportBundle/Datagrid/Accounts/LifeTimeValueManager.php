<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Accounts;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;

class LifeTimeValueManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();

        $field->setName('name');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => $this->translate('orocrm.report.datagrid.columns.account_name'),
                'entity_alias' => 'a',
                'field_name'   => 'name',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('value');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DECIMAL,
                'label'       => $this->translate('orocrm.report.datagrid.columns.total_value'),
                'field_name'  => 'value',
                'expression'  => 'value',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('close_date');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => $this->translate('orocrm.report.datagrid.columns.close_date'),
                'field_name'  => 'closeDate',
                'filter_type' => FilterInterface::TYPE_DATE,
                'required'    => false,
                'sortable'    => false,
                'show_column' => false,
                'filterable'  => true,
                'show_filter' => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('created_at');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => $this->translate('orocrm.report.datagrid.columns.created_date'),
                'field_name'  => 'createdAt',
                'filter_type' => FilterInterface::TYPE_DATE,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => true,
                'show_column' => false,
                'show_filter' => true,
            )
        );

        $fieldsCollection->add($field);
    }
}
