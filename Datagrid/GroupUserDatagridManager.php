<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Doctrine\ORM\QueryBuilder;

class GroupUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var Group
     */
    private $group;

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
    protected function createQuery()
    {
        /** @var $query QueryBuilder */
        $query = parent::createQuery();
        $query->addSelect(
            'CASE WHEN ' .
            '(:group MEMBER OF u.groups OR u.id IN (:data_in)) AND u.id NOT IN (:data_not_in) '.
            'THEN 1 ELSE 0 END AS hasCurrentGroup'
        );
        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array_merge(
            parent::getQueryParameters(),
            array('group' => $this->getGroup())
        );
    }
}
