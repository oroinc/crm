<?php

namespace Oro\Bundle\AccountBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\CallbackProperty;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\PhoneType;

class AccountDatagridManager extends FlexibleDatagridManager
{
    protected $excludeAttributes = array(
        'emails',
        'phones'
    );

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'oro_account_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_account_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_account', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getFlexibleAttributes()
    {
        parent::getFlexibleAttributes();

        // exclude collections attributes from grid
        foreach ($this->excludeAttributes as $attributeCode) {
            if (isset($this->attributes[$attributeCode])) {
                unset($this->attributes[$attributeCode]);
            }
        }

        return $this->attributes;
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
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

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
        $fieldsCollection->add($fieldName);

        $fieldPhone = new FieldDescription();
        $fieldPhone->setName('office_phone');
        $fieldPhone->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Phone',
                'field_name'  => 'phones',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $phoneProperty = new CallbackProperty(
            $fieldPhone->getName(),
            function (ResultRecordInterface $record) {
                $phones = $record->getValue('phones')->getData();
                /** @var $phone Collection */
                foreach ($phones as $phone) {
                    if ($phone->getType() == PhoneType::TYPE_OFFICE) {
                        return $phone->getData();
                    }
                }
                return null;
            }
        );
        $fieldPhone->setProperty($phoneProperty);
        $fieldsCollection->add($fieldPhone);

        $this->configureFlexibleField($fieldsCollection, 'email', array('show_filter' => true));

        $addressOptions = array(
            'type'        => FieldDescriptionInterface::TYPE_TEXT,
            'filter_type' => FilterInterface::TYPE_STRING,
            'sortable'    => false,
            'filterable'  => false
        );
        $this->configureFlexibleField($fieldsCollection, 'shipping_address', $addressOptions);
        $this->configureFlexibleField($fieldsCollection, 'billing_address', $addressOptions);

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
                'label'         => 'View',
                'link'          => 'view_link',
                'runOnRowClick' => true,
                'backUrl' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label' => 'View',
                'icon'  => 'user',
                'link'  => 'view_link',
                'backUrl' => true,
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'root',
            'options'      => array(
                'label'   => 'Update',
                'icon'    => 'edit',
                'link'    => 'update_link',
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

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }
}
