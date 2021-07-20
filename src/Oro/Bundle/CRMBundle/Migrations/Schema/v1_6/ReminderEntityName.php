<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class ReminderEntityName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateRelatedEntityClassName($queries);
    }

    public static function updateRelatedEntityClassName(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_reminder',
            'related_entity_classname',
            'OroCRM',
            'Oro'
        ));
    }
}
