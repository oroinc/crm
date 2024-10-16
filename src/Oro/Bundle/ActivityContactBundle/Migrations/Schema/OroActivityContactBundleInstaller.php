<?php

namespace Oro\Bundle\ActivityContactBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityContactBundle\Migrations\Schema\v1_0\OroActivityContactBundle;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroActivityContactBundleInstaller implements Installation
{
    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_1';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        OroActivityContactBundle::removeUserACFields($schema, $queries);
    }
}
