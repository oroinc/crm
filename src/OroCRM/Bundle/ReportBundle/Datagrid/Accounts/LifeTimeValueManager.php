<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Accounts;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;

class LifeTimeValueManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();

        $field->setName('username');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Username',
                'entity_alias' => 'u',
                'field_name'   => 'username',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('firstname');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'First name',
                'entity_alias' => 'u',
                'field_name'   => 'firstName',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);

        $field = new FieldDescription();

        $field->setName('nameCount');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'Name count',
                'field_name'   => 'cnt',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'expression'   => 'COUNT(u.firstName)',
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );

        $fieldsCollection->add($field);
    }
}
