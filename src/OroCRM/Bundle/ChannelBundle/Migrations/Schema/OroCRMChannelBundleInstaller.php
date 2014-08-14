<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Installation;

use OroCRM\Bundle\ChannelBundle\Migrations\Schema\v1_0\OroCRMChannelBundle;

class OroCRMChannelBundleInstaller implements Installation
{
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $migration = new OroCRMChannelBundle();
        $migration->up($schema, $queries);
    }
}
