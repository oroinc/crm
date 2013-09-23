<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Opportunities;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Property\CallbackProperty;

use OroCRM\Bundle\ReportBundle\Filter\PeriodFilter;
use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;

class WonByPeriodManager extends ReportGridManagerAbstract
{
    const PERIOD_COLUMN_NAME          = 'period';
    const PERIOD_FILTER_DEFAULT_VALUE = 'monthPeriod';

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {
        $field = new FieldDescription();

        $field->setName(self::PERIOD_COLUMN_NAME);
        $field->setOptions(
            array(
                'type'             => FieldDescriptionInterface::TYPE_TEXT,
                'label'            => $this->translate('orocrm.report.datagrid.columns.period'),
                'expression'       => self::PERIOD_COLUMN_NAME,
                'required'         => true,
                'sortable'         => true,
                'show_column'      => true,
                'filter_type'      => PeriodFilter::SERVICE_NAME,
                'filterable'       => true,
                'show_filter'      => true,
                'choices'          => array(
                    'monthPeriod'   => 'Monthly',
                    'quarterPeriod' => 'Quarterly',
                    'yearPeriod'    => 'Yearly'
                ),
                'populate_default' => false
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
            self::PERIOD_COLUMN_NAME => array(
                'value' => self::PERIOD_FILTER_DEFAULT_VALUE
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureDatagrid(DatagridInterface $datagrid)
    {
        parent::configureDatagrid($datagrid);

        $periodName = $datagrid->getForm()->get(self::PERIOD_COLUMN_NAME)->get('value')->getNormData()
            ? : self::PERIOD_FILTER_DEFAULT_VALUE;

        // dynamically get needed column value
        $datagrid->addProperty(
            new CallbackProperty(
                self::PERIOD_COLUMN_NAME,
                function (ResultRecord $record) use ($periodName) {
                    return $record->getValue($periodName);
                }
            )
        );

        /** @var FieldDescriptionInterface $field */
        $originalField = $this->getFieldDescriptionCollection()->get(self::PERIOD_COLUMN_NAME);
        $field         = clone $originalField;
        $field->setName($periodName);
        $fieldMapping = array(
            'fieldName'       => $periodName,
            'fieldExpression' => $periodName
        );
        $field->setFieldMapping($fieldMapping);

        // change sorter field name
        $sorter = $datagrid->getSorter(self::PERIOD_COLUMN_NAME);
        $sorter->initialize($field, $sorter->getDirection());
        $datagrid->addSorter($sorter);
    }
}
