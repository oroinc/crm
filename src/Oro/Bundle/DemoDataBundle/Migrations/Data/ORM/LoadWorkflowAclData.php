<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

/**
 * Loads CRM workflows ACL data
 */
class LoadWorkflowAclData extends LoadAclRolesData
{
    /**
     * {@inheritdoc}
     */
    protected function getDataPath()
    {
        return '@OroDemoDataBundle/Migrations/Data/ORM/CrmRoles/workflows.yml';
    }
}
