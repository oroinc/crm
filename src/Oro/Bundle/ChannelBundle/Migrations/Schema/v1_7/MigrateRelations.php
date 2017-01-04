<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateRelations implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'orocrm_channel',
            'customer_identity',
            'OroCRM',
            'Oro'
        ));

        $queries->addQuery(new UpdateTableFieldQuery(
            'orocrm_channel_entity_name',
            'name',
            'OroCRM',
            'Oro'
        ));
    }
}
