<?php

namespace Oro\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\CallbackProperty;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\PhoneType;
use Doctrine\ORM\PersistentCollection;

class ContactDatagridManager extends FlexibleDatagridManager
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
            new UrlProperty('view_link', $this->router, 'oro_contact_view', array('id')),
            new UrlProperty('update_link', $this->router, 'oro_contact_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_contact', array('id')),
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

        $this->configureFlexibleField($fieldsCollection, 'first_name');
        $this->configureFlexibleField($fieldsCollection, 'last_name');

        $fieldPhone = new FieldDescription();
        $fieldPhone->setName('office_phone');
        $fieldPhone->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Phone',
                'field_name'  => 'phones',
                'filter_type' => FilterInterface::TYPE_STRING,
            )
        );
        $phoneProperty = new CallbackProperty(
            $fieldPhone->getName(),
            function (ResultRecordInterface $record) use ($fieldPhone) {
                $phones = $record->getValue($fieldPhone->getFieldName())->getData();
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

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Email',
                'field_name'  => 'emails',
                'filter_type' => FilterInterface::TYPE_STRING,
            )
        );
        $emailProperty = new CallbackProperty(
            $fieldEmail->getName(),
            function (ResultRecordInterface $record) use ($fieldEmail) {
                /** @var $emails PersistentCollection */
                $emails = $record->getValue($fieldEmail->getFieldName())->getData();
                if (!empty($emails)) {
                    /** @var $email Collection */
                    $email = $emails->first();
                    return $email->getData();
                }
                return null;
            }
        );
        $fieldEmail->setProperty($emailProperty);
        $fieldsCollection->add($fieldEmail);

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

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        /** @var $query QueryBuilder */
        $query = parent::createQuery();
        $query->leftJoin('Value.account', 'a');
        $query->addSelect('a');
        $query->addSelect('a.name as accountName');

        return $query;
    }
}
