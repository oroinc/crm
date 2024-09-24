<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

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
