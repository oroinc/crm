<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class AccountDatagridManager extends FlexibleDatagridManager
{
    /**
     * @var string
     */
    protected $ownerExpression = "CONCAT(accountOwner.firstName, CONCAT(' ', accountOwner.lastName))";

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_account_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_account_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_account', array('id')),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldName = new FieldDescription();
        $fieldName->setName('name');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.account.datagrid.account_name'),
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('contact_first_name');
        $fieldFirstName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('First Name'),
                'field_name'  => 'contactFirstName',
                'expression'  => 'defaultContact.firstName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldFirstName);

        $fieldLastName = new FieldDescription();
        $fieldLastName->setName('contact_last_name');
        $fieldLastName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Last Name'),
                'field_name'  => 'contactLastName',
                'expression'  => 'defaultContact.lastName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldLastName);

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Email'),
                'field_name'      => 'contactEmail',
                'expression'      => 'defaultContactEmail.email',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
            )
        );
        $fieldsCollection->add($fieldEmail);

        $fieldPhone = new FieldDescription();
        $fieldPhone->setName('phone');
        $fieldPhone->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('Phone'),
                'field_name'      => 'contactPhone',
                'expression'      => 'defaultContactPhone.phone',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
            )
        );
        $fieldsCollection->add($fieldPhone);

        $fieldContactName = new FieldDescription();
        $fieldContactName->setName('owner');
        $fieldContactName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('Owner'),
                'field_name'  => 'ownerName',
                'expression'  => $this->ownerExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($fieldContactName);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('Created At'),
                'field_name'  => 'created',
                'filter_type' => FilterInterface::TYPE_DATETIME,
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
                'label'       => $this->translate('Updated At'),
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
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
            'acl_resource' => 'orocrm_account_view',
            'options'      => array(
                'label'         => $this->translate('View'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_account_view',
            'options'      => array(
                'label' => $this->translate('View'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_account_update',
            'options'      => array(
                'label'   => $this->translate('Update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'orocrm_account_remove',
            'options'      => array(
                'label' => $this->translate('Delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }

    /**
     * @param ProxyQueryInterface $query
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $this->applyJoinWithDefaultContact($query);
    }

    /**
     * @param ProxyQueryInterface $query
     */
    protected function applyJoinWithDefaultContact(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query
            ->leftJoin("$entityAlias.defaultContact", 'defaultContact')
            ->leftJoin("defaultContact.emails", 'defaultContactEmail', 'WITH', 'defaultContactEmail.primary = true')
            ->leftJoin("defaultContact.phones", 'defaultContactPhone', 'WITH', 'defaultContactPhone.primary = true')
            ->leftJoin("$entityAlias.owner", 'accountOwner');

        $query->addSelect('defaultContact.firstName as contactFirstName', true);
        $query->addSelect('defaultContact.lastName as contactLastName', true);
        $query->addSelect('defaultContactEmail.email as contactEmail', true);
        $query->addSelect('defaultContactPhone.phone as contactPhone', true);
        $query->addSelect($this->ownerExpression . ' AS ownerName', true);
    }
}
