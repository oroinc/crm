<?php

namespace Oro\Bridge\TaskCRM\Migrations\Data\ORM;

use Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadAclRolesData;

/**
 * Loads task_flow ACL data
 */
class LoadWorkflowAclData extends LoadAclRolesData
{
    #[\Override]
    protected function getDataPath()
    {
        return '@OroTaskCRMBridgeBundle/Migrations/Data/ORM/CrmRoles/workflows.yml';
    }
}
