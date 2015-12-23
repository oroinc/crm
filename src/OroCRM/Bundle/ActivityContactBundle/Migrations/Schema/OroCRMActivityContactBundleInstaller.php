<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\ActivityContactBundle\Migrations\Schema\v1_0\OroCRMActivityContactBundle;

class OroCRMActivityContactBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroCRMActivityContactBundle::removeUserACFields($schema, $queries);
    }
}
