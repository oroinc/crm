<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class RoleUserDatagridManager extends DatagridManager
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $fieldsCollection;

    /**
     * @var Router
     */
    protected $router;

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

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
                    'filterable'  => true,
                    'show_filter' => false,
                )
            );
        }

        return $this->fieldsCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListFields()
    {
        return $this->getFieldDescriptionCollection()->getElements();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isSortable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
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
            )
        );

        return array($clickAction);
    }

}
