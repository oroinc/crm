<?php

namespace Oro\Bundle\AccountBundle\Datagrid;

use Symfony\Component\Routing\Router;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class AccountDatagridManager extends FlexibleDatagridManager
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
            new UrlProperty('show_link', $this->router, 'oro_account_show', array('id')),
            new UrlProperty('edit_link', $this->router, 'oro_account_edit', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_account', array('id')),
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
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($fieldId);

            $fieldName = new FieldDescription();
            $fieldName->setName('name');
            $fieldName->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'       => 'Name',
                    'field_name'  => 'name',
                    'filter_type' => FilterInterface::TYPE_STRING,
                    'required'    => false,
                    'sortable'    => true,
                    'filterable'  => true,
                    'show_filter' => true,
                )
            );
            $this->fieldsCollection->add($fieldName);

            $specialAttributes = array('shipping_address', 'billing_address');
            foreach ($this->getFlexibleAttributes() as $attribute) {
                if (in_array($attribute->getCode(), $specialAttributes)) {
                    $attributeType = FieldDescriptionInterface::TYPE_TEXT;
                    $filterType = FilterInterface::TYPE_STRING;
                    $isSortable = false;
                    $isSearchable = false;
                } else {
                    $backendType   = $attribute->getBackendType();
                    $attributeType = $this->convertFlexibleTypeToFieldType($backendType);
                    $filterType    = $this->convertFlexibleTypeToFilterType($backendType);
                    $isSortable = true;
                    $isSearchable = true;
                }
                $field = new FieldDescription();
                $field->setName($attribute->getCode());
                $field->setOptions(
                    array(
                        'type'          => $attributeType,
                        'label'         => ucfirst($attribute->getCode()),
                        'field_name'    => $attribute->getCode(),
                        'filter_type'   => $filterType,
                        'required'      => false,
                        'sortable'      => $isSortable,
                        'filterable'    => $isSearchable,
                        'flexible_name' => $this->flexibleManager->getFlexibleName()
                    )
                );
                $this->fieldsCollection->add($field);
            }

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
            $this->fieldsCollection->add($fieldCreated);

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
            $this->fieldsCollection->add($fieldUpdated);
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
    protected function getFilters()
    {
        $fields = array();
        /** @var $fieldDescription FieldDescription */
        foreach ($this->getFieldDescriptionCollection() as $fieldDescription) {
            if ($fieldDescription->isFilterable()) {
                $fields[] = $fieldDescription;
            }
        }

        return $fields;
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
                'route'         => 'oro_account_show',
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
