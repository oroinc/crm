<?php

namespace Oro\Bundle\DataAuditBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\PropertyInterface;

use Oro\Bundle\DataAuditBundle\Datagrid\DataProperty;

class LogDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        $this->fieldsCollection = new FieldDescriptionCollection();

        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'ID',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldId);

        $fieldAction = new FieldDescription();
        $fieldAction->setName('action');
        $fieldAction->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Action',
                'field_name'  => 'action',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldAction);

        $fieldVersion = new FieldDescription();
        $fieldVersion->setName('version');
        $fieldVersion->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Version',
                'field_name'  => 'version',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldVersion);

        $fieldLogged = new FieldDescription();
        $fieldLogged->setName('logged');
        $fieldLogged->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Logged At',
                'field_name'  => 'logged_at',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldLogged);

        $fieldObjectClass = new FieldDescription();
        $fieldObjectClass->setName('objectClass');
        $fieldObjectClass->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Object Class',
                'field_name'  => 'object_class',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $this->fieldsCollection->add($fieldObjectClass);

        $fieldObjectId = new FieldDescription();
        $fieldObjectId->setName('objectId');
        $fieldObjectId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'Object Id',
                'field_name'  => 'object_id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldObjectId);

        $fieldUserId = new FieldDescription();
        $fieldUserId->setName('user');
        $fieldUserId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'User',
                'field_name'  => 'user',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldUserId);

        $fieldData = new FieldDescription();
        $fieldData->setName('data');
        $fieldData->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Data',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $this->fieldsCollection->add($fieldData);

        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }
}
