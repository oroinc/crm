<?php

namespace Oro\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

use Oro\Bundle\ContactBundle\Entity\Group;

class GroupContactDatagridManager extends FlexibleDatagridManager
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
        $fieldsCollection->add($this->createUserRelationColumn());

        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'ID',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_column' => false,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldId);

        $this->configureFlexibleField($fieldsCollection, 'first_name', array('label' => 'First Name'));
        $this->configureFlexibleField($fieldsCollection, 'last_name', array('label' => 'Last Name'));
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
    protected function createUserRelationColumn()
    {
        $fieldHasGroup = new FieldDescription();
        $fieldHasGroup->setName('has_group');
        $fieldHasGroup->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => 'Has group',
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

        return array(
            'data_in'     => $dataIn,
            'data_not_in' => $dataNotIn,
            'group'       => $this->getGroup()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        /** @var $query QueryBuilder */
        $query = parent::createQuery();
        $query->addSelect(
            'CASE WHEN ' .
            '(:group MEMBER OF c.groups OR c.id IN (:data_in)) AND c.id NOT IN (:data_not_in) '.
            'THEN 1 ELSE 0 END AS hasCurrentGroup'
        );
        return $query;
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
