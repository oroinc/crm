<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class LightUserDatagridManager extends UserDatagridManager
{
    protected function getProperties()
    {
        return array(
            new UrlProperty('show_link', $this->router, 'oro_user_show', array('id')),
        );
    }

    /**
     * @return FieldDescriptionCollection
     */
    protected function getFieldDescriptionCollection()
    {
        if (!$this->fieldsCollection) {
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
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );
            $this->fieldsCollection->add($fieldId);

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
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );
            $this->fieldsCollection->add($fieldUsername);

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
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );
            $this->fieldsCollection->add($fieldEmail);

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
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );
            $this->fieldsCollection->add($fieldFirstName);

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
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );
            $this->fieldsCollection->add($fieldLastName);
        }

        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        return array();
    }

}
