<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

/**
 * Provides common functionality for loading ACL data for demo roles.
 *
 * This base class ensures that ACL permissions are loaded after demo roles are created
 * by declaring the appropriate migration dependency. Subclasses should extend this
 * to load specific ACL configurations for their demo data roles.
 */
abstract class LoadAclRolesData extends AbstractLoadAclData
{
    #[\Override]
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData',
        ];
    }
}
