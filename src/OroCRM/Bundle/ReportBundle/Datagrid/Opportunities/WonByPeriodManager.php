<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Opportunities;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Oro\Bundle\GridBundle\Property\CallbackProperty;
use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;
use OroCRM\Bundle\ReportBundle\Filter\PeriodFilter;

class WonByPeriodManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {
        $field = new FieldDescription();

        $periodName = 'yearPeriod';
        $field->setName('period');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.report.datagrid.columns.period'),
                'expression'  => 'period',
                'required'    => true,
                'sortable'    => true,
                'show_column' => true,
                'filter_type' => PeriodFilter::SERVICE_NAME,
                'filterable'  => true,
                'show_filter' => true,
                'choices'     => array(
                    'monthPeriod'   => 'Monthly',
                    'quarterPeriod' => 'Quarterly',
                    'yearPeriod'    => 'Yearly'
                )
            )
        );
        $field->setProperty(
            new CallbackProperty(
                'period',
                function (ResultRecord $record) use ($periodName) {
                    return $record->getValue($periodName);
                }
            )
        );

        $fieldCollection->add($field);

        $field = new FieldDescription();
        $field->setName('cnt');
        $field->setOptions(
            array(
                'type'             => FieldDescriptionInterface::TYPE_INTEGER,
                'label'            => $this->translate('orocrm.report.datagrid.columns.number_won'),
                'expression'       => 'cnt',
                'filter_by_having' => true,
                'filter_type'      => FilterInterface::TYPE_NUMBER,
                'required'         => false,
                'sortable'         => true,
                'filterable'       => true,
                'show_filter'      => true,
            )
        );
        $fieldCollection->add($field);


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

        $fieldCollection->add($field);

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

        $fieldCollection->add($field);

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

        $fieldCollection->add($field);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFilters()
    {
        return array(
            'period' => array(
                'value' => 'monthPeriod'
            )
        );
    }
}
