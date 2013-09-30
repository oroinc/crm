<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\Datagrid\AbstractDatagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class LeadDatagridManager extends AbstractDatagrid
{
    /**
     * Expression to get region text or label, CONCAT is used as type cast function
     *
     * @var string
     */
    protected $regionExpression
        = "CONCAT(CASE WHEN address.stateText IS NOT NULL THEN address.stateText ELSE region.name END, '')";

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_sales_lead_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_sales_lead_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_lead', array('id')),
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
                'label'       => $this->translate('orocrm.sales_lead.datagrid.name'),
                'field_name'  => 'name',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldName);

        $fieldStatus = new FieldDescription();
        $fieldStatus->setName('status');
        $fieldStatus->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales_lead.datagrid.status'),
                'field_name'      => 'status',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sort_field_mapping' => array(
                    'entityAlias' => 'status',
                    'fieldName'   => 'label',
                ),
                'sortable'        => true,
                'filterable'      => true,
                // entity filter options
                'class'           => 'OroCRMSalesBundle:LeadStatus',
                'property'        => 'label',
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($fieldStatus);

        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('first_name');
        $fieldFirstName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.sales_lead.datagrid.first_name'),
                'field_name'  => 'firstName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldFirstName);

        $fieldLastName = new FieldDescription();
        $fieldLastName->setName('last_name');
        $fieldLastName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.sales_lead.datagrid.last_name'),
                'field_name'  => 'lastName',
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
                'label'           => $this->translate('orocrm.sales_lead.datagrid.email'),
                'field_name'      => 'email',
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
                'label'           => $this->translate('orocrm.sales_lead.datagrid.phone'),
                'field_name'      => 'phoneNumber',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'show_filter'     => true,
            )
        );
        $fieldsCollection->add($fieldPhone);

        $fieldCountry = new FieldDescription();
        $fieldCountry->setName('country');
        $fieldCountry->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales_lead.datagrid.country'),
                'field_name'      => 'countryName',
                'expression'      => 'address.country',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sortable'        => true,
                'filterable'      => true,
                // entity filter options
                'multiple'        => true,
                'class'           => 'OroAddressBundle:Country',
                'property'        => 'name',
                'query_builder'   => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'translatable'    => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldCountry);

        $fieldRegion = new FieldDescription();
        $fieldRegion->setName('region');
        $fieldRegion->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales_lead.datagrid.region'),
                'field_name'      => 'regionLabel',
                'expression'      => $this->regionExpression,
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldRegion);

        $fieldPostalCode = new FieldDescription();
        $fieldPostalCode->setName('postal_code');
        $fieldPostalCode->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.sales_lead.datagrid.postal_code'),
                'field_name'      => 'addressPostalCode',
                'expression'      => 'address.postalCode',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldPostalCode);

        $this->addDynamicFields();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_lead_view',
            'options'      => array(
                'label'         => $this->translate('orocrm.sales_lead.datagrid.view'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_lead_view',
            'options'      => array(
                'label' => $this->translate('orocrm.sales_lead.datagrid.view'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_sales_lead_update',
            'options'      => array(
                'label'   => $this->translate('orocrm.sales_lead.datagrid.update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'orocrm_sales_lead_delete',
            'options'      => array(
                'label' => $this->translate('orocrm.sales_lead.datagrid.delete'),
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
        $this->applyJoinWithAddressAndCountry($query);
    }

    /**
     * @param ProxyQueryInterface $query
     */
    protected function applyJoinWithAddressAndCountry(ProxyQueryInterface $query)
    {
        // need to translate countries
        $query->setQueryHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\Translatable\Query\TreeWalker\TranslationWalker'
        );

        $entityAlias = $query->getRootAlias();

        /** @var $query QueryBuilder */
        $query
            ->leftJoin("$entityAlias.status", 'status')
            ->leftJoin("$entityAlias.address", 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'region');

        $query->addSelect('status.label as statusLabel', true);
        $query->addSelect('country.name as countryName', true);
        $query->addSelect('address.postalCode as addressPostalCode', true);
        $query->addSelect($this->regionExpression . ' AS regionLabel', true);
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'first_name' => SorterInterface::DIRECTION_ASC,
            'last_name' => SorterInterface::DIRECTION_ASC,
        );
    }
}
