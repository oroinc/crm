<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use Oro\Bundle\GridBundle\Property\FixedProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class ContactDatagridManager extends FlexibleDatagridManager
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
            new UrlProperty('view_link', $this->router, 'orocrm_contact_view', array('id')),
            new UrlProperty('update_link', $this->router, 'orocrm_contact_update', array('id')),
            new UrlProperty('delete_link', $this->router, 'oro_api_delete_contact', array('id')),
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => $this->translate('orocrm.contact.datagrid.contact_id'),
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

        $this->configureFlexibleField($fieldsCollection, 'first_name', array('show_filter' => true));
        $this->configureFlexibleField($fieldsCollection, 'last_name', array('show_filter' => true));
        $this->configureFlexibleField($fieldsCollection, 'email', array('show_filter' => true));
        $this->configureFlexibleField($fieldsCollection, 'phone', array('show_filter' => true));

        $rolesLabel = new FieldDescription();
        $rolesLabel->setName('groups');
        $rolesLabel->setProperty(new FixedProperty('groups', 'groupLabelsAsString'));
        $rolesLabel->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.contact.datagrid.groups'),
                'field_name'      => 'groups',
                'expression'      => 'contactGroup',
                'filter_type'     => FilterInterface::TYPE_ENTITY,
                'sort_field_mapping' => array(
                    'entityAlias' => 'contactGroup',
                    'fieldName'   => 'label',
                ),
                'sortable'        => true,
                'filterable'      => true,
                // entity filter options
                'class'           => 'OroCRMContactBundle:Group',
                'property'        => 'label',
                'filter_by_where' => true
            )
        );
        $fieldsCollection->add($rolesLabel);

        $this->configureFlexibleField($fieldsCollection, 'source');

        $fieldCountry = new FieldDescription();
        $fieldCountry->setName('country');
        $fieldCountry->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_TEXT,
                'label'           => $this->translate('orocrm.contact.datagrid.country'),
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
                'label'           => $this->translate('orocrm.contact.datagrid.region'),
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
                'label'           => $this->translate('orocrm.contact.datagrid.postal_code'),
                'field_name'      => 'addressPostalCode',
                'expression'      => 'address.postalCode',
                'filter_type'     => FilterInterface::TYPE_STRING,
                'sortable'        => true,
                'filterable'      => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldPostalCode);

        $fieldCreated = new FieldDescription();
        $fieldCreated->setName('created');
        $fieldCreated->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => $this->translate('orocrm.contact.datagrid.created_at'),
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
                'label'       => $this->translate('orocrm.contact.datagrid.updated_at'),
                'field_name'  => 'updated',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'sortable'    => true,
                'filterable'  => true,
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
            'acl_resource' => 'orocrm_contact_view',
            'options'      => array(
                'label'         => $this->translate('orocrm.contact.datagrid.view'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        $viewAction = array(
            'name'         => 'view',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_contact_view',
            'options'      => array(
                'label' => $this->translate('orocrm.contact.datagrid.view'),
                'icon'  => 'user',
                'link'  => 'view_link',
            )
        );

        $updateAction = array(
            'name'         => 'update',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_contact_update',
            'options'      => array(
                'label'   => $this->translate('orocrm.contact.datagrid.update'),
                'icon'    => 'edit',
                'link'    => 'update_link',
            )
        );

        $deleteAction = array(
            'name'         => 'delete',
            'type'         => ActionInterface::TYPE_DELETE,
            'acl_resource' => 'orocrm_contact_delete',
            'options'      => array(
                'label' => $this->translate('orocrm.contact.datagrid.delete'),
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
            ->leftJoin("$entityAlias.addresses", 'address', 'WITH', 'address.primary = true')
            ->leftJoin("$entityAlias.groups", 'contactGroup')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'region');

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
