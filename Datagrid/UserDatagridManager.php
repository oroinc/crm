<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class UserDatagridManager extends FlexibleDatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('show_link', $this->router, 'oro_user_show', array('id')),
            new UrlProperty('edit_link', $this->router, 'oro_user_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_profile', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
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
        $fieldsCollection->add($fieldId);

        $fieldUsername = new FieldDescription();
        $fieldUsername->setName('username');
        $fieldUsername->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Username',
                'field_name'  => 'username',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUsername);

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Email',
                'field_name'  => 'email',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldEmail);

        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('firstName');
        $fieldFirstName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'First name',
                'field_name'  => 'firstName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldFirstName);

        $fieldLastName = new FieldDescription();
        $fieldLastName->setName('lastName');
        $fieldLastName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Last name',
                'field_name'  => 'lastName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldLastName);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Created At',
                'field_name'  => 'created',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldCreated);

        $fieldUpdated = new FieldDescription();
        $fieldUpdated->setName('updated');
        $fieldUpdated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Updated At',
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUpdated);

        $fieldUpdated = new FieldDescription();
        $fieldUpdated->setName('status');
        $fieldUpdated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Status',
                'field_name'  => 'currentStatus',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldUpdated);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'         => 'Show',
                'link'          => 'show_link',
                'route'         => 'oro_user_show',
                'runOnRowClick' => true,
                'backUrl' => true,
            )
        );

        $showAction = array(
            'name'         => 'show',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Show',
                'icon'  => 'user',
                'link'  => 'show_link',
                'backUrl' => true,
            )
        );

        $editAction = array(
            'name'         => 'edit',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => 'Edit',
                'icon'    => 'edit',
                'link'    => 'edit_link',
                'backUrl' => true,
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'Delete',
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $showAction, $editAction, $deleteAction);
    }
}
