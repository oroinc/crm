<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupContactDatagridManager extends DatagridManager
{
    /**
     * @var Group
     */
    private $group;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldsCollection->add($this->createContactRelationColumn());

        $fieldFirstName = new FieldDescription();
        $fieldFirstName->setName('first_name');
        $fieldFirstName->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => $this->translate('orocrm.contact.datagrid.first_name'),
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
                'label'       => $this->translate('orocrm.contact.datagrid.last_name'),
                'field_name'  => 'lastName',
                'filter_type' => FilterInterface::TYPE_STRING,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($fieldLastName);
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     * @throws \LogicException When group is not set
     */
    public function getGroup()
    {
        if (!$this->group) {
            throw new \LogicException('Datagrid manager has no configured Group entity');
        }
        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    protected function createContactRelationColumn()
    {
        $fieldHasGroup = new FieldDescription();
        $fieldHasGroup->setName('has_group');
        $fieldHasGroup->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => $this->translate('Has group'),
                'field_name'  => 'hasCurrentGroup',
                'expression'  => 'hasCurrentGroup',
                'nullable'    => false,
                'editable'    => true,
                'sortable'    => true,
                'filter_type' => FilterInterface::TYPE_BOOLEAN,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        return $fieldHasGroup;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $dataIn    = !empty($additionalParameters['data_in']) ? $additionalParameters['data_in'] : array(0);
        $dataNotIn = !empty($additionalParameters['data_not_in']) ? $additionalParameters['data_not_in'] : array(0);

        $parameters = array(
            'data_in'     => $dataIn,
            'data_not_in' => $dataNotIn
        );
        if ($this->getGroup()->getId()) {
            $parameters['group'] = $this->getGroup();
        }

        return $parameters;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $entityAlias = $query->getRootAlias();

        if ($this->getGroup()->getId()) {
            $query->addSelect(
                "CASE WHEN " .
                "(:group MEMBER OF $entityAlias.groups OR $entityAlias.id IN (:data_in)) " .
                "AND $entityAlias.id NOT IN (:data_not_in) ".
                "THEN 1 ELSE 0 END AS hasCurrentGroup",
                true
            );
        } else {
            $query->addSelect(
                "CASE WHEN " .
                "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) ".
                "THEN 1 ELSE 0 END AS hasCurrentGroup",
                true
            );
        }
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_group' => SorterInterface::DIRECTION_DESC,
            'last_name' => SorterInterface::DIRECTION_ASC,
        );
    }
}
