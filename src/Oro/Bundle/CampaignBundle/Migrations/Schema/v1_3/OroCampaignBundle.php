<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmCampaignEmailTable($schema);
        $this->createOrocrmEmailCampaignStatisticsTable($schema);
        $this->createOrocrmCmpgnTransportStngsTable($schema);
        $this->updateOrocrmCmpgnTransportStngsTableAddInternalEmailTransport($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCampaignEmailForeignKeys($schema);
        $this->addOrocrmEmailCampaignStatisticsForeignKeys($schema);
        $this->addOrocrmCmpgnTransportStngsForeignKeysForInternalTransport($schema);
    }

    /**
     * Create oro_campaign_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCampaignEmailTable(Schema $schema)
    {
        $table = $schema->createTable('oro_campaign_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('transport_settings_id', 'integer', ['notnull' => false]);
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('is_sent', 'boolean', []);
        $table->addColumn('schedule', 'string', ['length' => 255]);
        $table->addColumn('scheduled_for', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('sender_email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('sent_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('sender_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('transport', 'string', ['length' => 255]);
        $table->addUniqueIndex(['transport_settings_id'], 'uniq_6cd4c1e1cffa7b8f');
        $table->addIndex(['marketing_list_id'], 'idx_6cd4c1e196434d04', []);
        $table->addIndex(['owner_id'], 'idx_6cd4c1e17e3c61f9', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['campaign_id'], 'idx_6cd4c1e1f639f774', []);
    }

    /**
     * Create oro_campaign_email_stats table
     *
     * @param Schema $schema
     */
    protected function createOrocrmEmailCampaignStatisticsTable(Schema $schema)
    {
        $table = $schema->createTable('oro_campaign_email_stats');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email_campaign_id', 'integer', []);
        $table->addColumn('marketing_list_item_id', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['marketing_list_item_id'], 'idx_31465f07d530662', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['email_campaign_id'], 'idx_31465f07e0f98bc3', []);
    }

    /**
     * Create oro_cmpgn_transport_stngs table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCmpgnTransportStngsTable(Schema $schema)
    {
        $table = $schema->createTable('oro_cmpgn_transport_stngs');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Update oro_cmpgn_transport_stngs table with internal transport settings.
     *
     * @param Schema $schema
     */
    protected function updateOrocrmCmpgnTransportStngsTableAddInternalEmailTransport(Schema $schema)
    {
        $table = $schema->getTable('oro_cmpgn_transport_stngs');
        $table->addColumn('email_template_id', 'integer', ['notnull' => false]);
        $table->addIndex(['email_template_id'], 'idx_16e86bf2131a730f', []);
    }

    /**
     * Add oro_campaign_email foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCampaignEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_campaign_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_cmpgn_transport_stngs'),
            ['transport_settings_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add oro_campaign_email_stats foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmEmailCampaignStatisticsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_campaign_email_stats');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_campaign_email'),
            ['email_campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_marketing_list_item'),
            ['marketing_list_item_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_cmpgn_transport_stngs internal trnasport foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCmpgnTransportStngsForeignKeysForInternalTransport(Schema $schema)
    {
        $table = $schema->getTable('oro_cmpgn_transport_stngs');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email_template'),
            ['email_template_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
