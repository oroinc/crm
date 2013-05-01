<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class RoleUserDatagridManager extends UserRelationDatagridManager
{
    /**
     * @var Role
     */
    private $role;

    /**
     * @param Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @return Role
     * @throws \LogicException When group is not set
     */
    public function getRole()
    {
        if (!$this->role) {
            throw new \LogicException('Datagrid manager has no configured Role entity');
        }
        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    protected function createUserRelationColumn()
    {
        $fieldHasRole = new FieldDescription();
        $fieldHasRole->setName('hasCurrentRole');
        $fieldHasRole->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'       => 'Has role',
                'field_name'  => 'hasCurrentRole',
                'expression'  => 'hasCurrentRole',
                'sortable'    => true,
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        return $fieldHasRole;
    }

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        $query = parent::createQuery();
        $query->addSelect('CASE WHEN :role MEMBER OF u.roles THEN 1 ELSE 0 END AS hasCurrentRole');
        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array(
            'role' => $this->getRole()
        );
    }
}
