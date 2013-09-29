<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\EntityBundle\Datagrid\AbstractDatagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class OpportunityDatagridManager extends AbstractDatagrid
{
    /**
     * @var string
     */
    protected $contactNameExpression = "CONCAT(contact.firstName, CONCAT(' ', contact.lastName))";

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_sales_opportunity_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_sales_opportunity_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_opportunity', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldName = new FieldDescription();
        $fieldName->setName('name');
        $fieldName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.name'),
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldContactName = new FieldDescription();
        $fieldContactName->setName('contact_name');
        $fieldContactName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.contact_name'),
                'field_name'  => 'contactName',
                'expression'  => $this->contactNameExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($fieldContactName);

        $fieldCloseDate = new FieldDescription();
        $fieldCloseDate->setName('close_date');
        $fieldCloseDate->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.close_date'),
                'field_name'  => 'closeDate',
                'filter_type' => FilterInterface::TYPE_DATE,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldCloseDate);

        $fieldProbability = new FieldDescription();
        $fieldProbability->setName('probability');
        $fieldProbability->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_PERCENT,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.probability'),
                'field_name'  => 'probability',
                'filter_type' => FilterInterface::TYPE_PERCENT,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldProbability);

        $fieldStatus = new FieldDescription();
        $fieldStatus->setName('status');
        $fieldStatus->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales.opportunity.datagrid.status'),
                'field_name'      => 'status',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sort_field_mapping' => array(
                    'entityAlias' => 'status',
                    'fieldName'   => 'label',
                ),
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
                // entity filter options
                'class'           => 'OroCRMSalesBundle:OpportunityStatus',
                'property'        => 'label',
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($fieldStatus);

        $fieldEmail = new FieldDescription();
        $fieldEmail->setName('email');
        $fieldEmail->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales.opportunity.datagrid.email'),
                'field_name'      => 'primaryEmail',
                'expression'      => 'email.email',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
            )
        );
        $fieldsCollection->add($fieldEmail);

        $this->addDynamicFields();
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_opportunity_view',
            'options'      => array(
                'label'         => $this->translate('orocrm.sales.opportunity.datagrid.view'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_opportunity_view',
            'options'      => array(
                'label' => $this->translate('orocrm.sales.opportunity.datagrid.view'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_opportunity_update',
            'options'      => array(
                'label'   => $this->translate('orocrm.sales.opportunity.datagrid.update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'orocrm_sales_opportunity_delete',
            'options'      => array(
                'label' => $this->translate('orocrm.sales.opportunity.datagrid.delete'),
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
        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query
            ->leftJoin("$entityAlias.contact", 'contact')
            ->leftJoin("$entityAlias.status", 'status')
            ->leftJoin('contact.emails', 'email', 'WITH', 'email.primary = true');

        $query->addSelect($this->contactNameExpression . ' AS contactName', true);
        $query->addSelect('status.label as statusLabel', true);
        $query->addSelect('email.email as primaryEmail', true);
    }
}
