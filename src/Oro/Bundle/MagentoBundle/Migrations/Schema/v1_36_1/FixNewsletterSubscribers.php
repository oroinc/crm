<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_36_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MagentoBundle\Migrations\Schema\v1_35_1\FixNewsletterSubscribers as CreateMissedData;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class FixNewsletterSubscribers implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // Add minimal id received during newsletter_subscriber_initial sync
        $table = $schema->getTable('oro_integration_transport');
        if (!$table->hasColumn('mage_newsl_subscr_synced_to_id')) {
            $migration = new CreateMissedData();
            $migration->up($schema, $queries);
        }
    }
}
