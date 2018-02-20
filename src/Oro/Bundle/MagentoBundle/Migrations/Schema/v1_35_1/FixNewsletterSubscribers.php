<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_35_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class FixNewsletterSubscribers implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // Add minimal id received during newsletter_subscriber_initial sync
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('mage_newsl_subscr_synced_to_id', 'integer', ['notnull' => false]);

        // Remove unique constraint on customer_id
        $table = $schema->getTable('orocrm_magento_newsl_subscr');
        $table->dropIndex('uniq_7c8eaa9395c3f3');
        $table->addIndex(['customer_id']);

        // Delete sync status for newsletter_subscriber_initial connector to be able to force it's update
        $sql = sprintf(
            "DELETE FROM oro_integration_channel_status WHERE connector = '%s'",
            InitialNewsletterSubscriberConnector::TYPE
        );
        $queries->addPostQuery(new SqlMigrationQuery($sql));
    }
}
