<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;

class OpportunityDatagridManager extends DatagridManager
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_opportunity_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_opportunity_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_opportunity', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldTopic = new FieldDescription();
        $fieldTopic->setName('topic');
        $fieldTopic->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.topic'),
                'field_name'  => 'topic',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldTopic);

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
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.probability'),
                'field_name'  => 'probability',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldProbability);

        $fieldBudgetAmount = new FieldDescription();
        $fieldBudgetAmount->setName('budget_amount');
        $fieldBudgetAmount->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DECIMAL,
                'label'       => $this->translate('orocrm.sales.opportunity.datagrid.budget_amount'),
                'field_name'  => 'budgetAmount',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldBudgetAmount);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_opportunity_view',
            'options'      => array(
                'label'         => $this->translate('orocrm.sales.opportunity.datagrid.view'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_opportunity_view',
            'options'      => array(
                'label' => $this->translate('orocrm.sales.opportunity.datagrid.view'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_opportunity_update',
            'options'      => array(
                'label'   => $this->translate('orocrm.sales.opportunity.datagrid.update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'orocrm_opportunity_delete',
            'options'      => array(
                'label' => $this->translate('orocrm.sales.opportunity.datagrid.delete'),
                'icon'  => 'trash',
                'link'  => 'delete_link',
            )
        );

        return array($clickAction, $viewAction, $updateAction, $deleteAction);
    }
}
