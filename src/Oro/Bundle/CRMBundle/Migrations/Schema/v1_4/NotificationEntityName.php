<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class NotificationEntityName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateTaggingEntityName($queries);
    }

    public static function updateTaggingEntityName(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_notification_email_notif',
            'entity_name',
            'OroCRM',
            'Oro'
        ));
    }
}
