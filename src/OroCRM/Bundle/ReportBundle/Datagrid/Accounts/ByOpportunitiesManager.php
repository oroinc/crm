<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Accounts;

use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ByOpportunitiesManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('name');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Account name',
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

        $this->addMoneyField('won', $fieldsCollection);
        $this->addMoneyField('lost', $fieldsCollection);
        $this->addMoneyField('in_progress', $fieldsCollection);

        $field = new FieldDescription();
        $field->setName('status');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Status',
                'entity_alias' => 's',
                'field_name'   => 'label',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('close_reason');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Close Reason',
                'entity_alias' => 'cr',
                'field_name'   => 'label',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('total_ops');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'Total opportunities',
                'field_name'   => 'total_ops',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * Add won field
     *
     * @param string $name won|lost|in_progress
     * @param FieldDescriptionCollection $fieldsCollection
     * @return $this
     */
    public function addMoneyField($name, FieldDescriptionCollection $fieldsCollection)
    {
        if (!in_array($name, array('won', 'lost', 'in_progress'))) {
            return $this;
        }

        $field = new FieldDescription();
        $field->setName($name);
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_DECIMAL,
                'label'        => ucfirst($name),
                'field_name'   => $name,
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        return $this;
    }
}
