<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMCampaignBundleInstaller implements Installation, VisitEventAssociationExtensionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_9';
    }

    /** @var VisitEventAssociationExtension */
    protected $extension;

    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmCampaignTable($schema);
        $this->createOrocrmCampaignEmailTable($schema);
        $this->createOrocrmEmailCampaignStatisticsTable($schema);
        $this->createOrocrmCampaignTeSummaryTable($schema);
        $this->createOrocrmCmpgnTransportStngsTable($schema);
        $this->updateOrocrmCmpgnTransportStngsTableAddInternalEmailTransport($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCampaignForeignKeys($schema);
        $this->addOrocrmCampaignEmailForeignKeys($schema);
        $this->addOrocrmEmailCampaignStatisticsForeignKeys($schema);
        $this->addOrocrmCampaignTeSummaryForeignKeys($schema);
        $this->addOrocrmCmpgnTransportStngsForeignKeysForInternalTransport($schema);

        $this->extension->addVisitEventAssociation($schema, 'orocrm_campaign');
    }

    /**
     * Create orocrm_campaign table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCampaignTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('combined_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('start_date', 'date', ['notnull' => false, 'comment' => '(DC2Type:date)']);
        $table->addColumn('end_date', 'date', ['notnull' => false, 'comment' => '(DC2Type:date)']);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn(
            'budget',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('report_period', 'string', ['length' => 25]);
        $table->addColumn('report_refresh_date', 'date', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'idx_55153cad7e3c61f9', []);
        $table->addIndex(['organization_id'], 'idx_e9a0640332c8a3de', []);
        $table->addUniqueIndex(['code'], 'uniq_e9a0640377153098');
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_campaign_email table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCampaignEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('transport_settings_id', 'integer', ['notnull' => false]);
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
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
        $table->addIndex(['organization_id'], 'idx_6cd4c1e132c8a3de', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['campaign_id'], 'idx_6cd4c1e1f639f774', []);
    }

    /**
     * Create orocrm_campaign_email_stats table
     *
     * @param Schema $schema
     */
    protected function createOrocrmEmailCampaignStatisticsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign_email_stats');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email_campaign_id', 'integer', []);
        $table->addColumn('marketing_list_item_id', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('open_count', 'integer', ['notnull' => false]);
        $table->addColumn('click_count', 'integer', ['notnull' => false]);
        $table->addColumn('bounce_count', 'integer', ['notnull' => false]);
        $table->addColumn('abuse_count', 'integer', ['notnull' => false]);
        $table->addColumn('unsubscribe_count', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['email_campaign_id'], 'idx_31465f07e0f98bc3', []);
        $table->addIndex(['marketing_list_item_id'], 'idx_31465f07d530662', []);
        $table->addIndex(['owner_id'], 'idx_3ce99ef07e3c61f9', []);
        $table->addIndex(['organization_id'], 'idx_3ce99ef032c8a3de', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email_campaign_id', 'marketing_list_item_id'], 'orocrm_ec_litem_unq');
    }

    /**
     * Create orocrm_campaign_te_summary table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCampaignTeSummaryTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign_te_summary');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('visit_count', 'integer', []);
        $table->addColumn('logged_at', 'date', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_id'], 'IDX_8F005FDD18F45C82', []);
        $table->addIndex(['name'], 'tes_event_name_idx', []);
        $table->addIndex(['logged_at'], 'tes_event_loggedAt_idx', []);
        $table->addIndex(['code'], 'tes_code_idx', []);
        $table->addIndex(['visit_count'], 'tes_visits_idx', []);
    }

    /**
     * Create orocrm_cmpgn_transport_stngs table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCmpgnTransportStngsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_cmpgn_transport_stngs');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Update orocrm_cmpgn_transport_stngs table with internal transport settings.
     *
     * @param Schema $schema
     */
    protected function updateOrocrmCmpgnTransportStngsTableAddInternalEmailTransport(Schema $schema)
    {
        $table = $schema->getTable('orocrm_cmpgn_transport_stngs');
        $table->addColumn('email_template_id', 'integer', ['notnull' => false]);
        $table->addIndex(['email_template_id'], 'idx_16e86bf2131a730f', []);
    }

    /**
     * Add orocrm_campaign foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCampaignForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
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
     * Add orocrm_campaign_email foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCampaignEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_cmpgn_transport_stngs'),
            ['transport_settings_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_campaign_email_stats foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmEmailCampaignStatisticsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign_email_stats');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign_email'),
            ['email_campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list_item'),
            ['marketing_list_item_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_campaign_te_summary foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCampaignTeSummaryForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign_te_summary');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orocrm_cmpgn_transport_stngs internal trnasport foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCmpgnTransportStngsForeignKeysForInternalTransport(Schema $schema)
    {
        $table = $schema->getTable('orocrm_cmpgn_transport_stngs');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email_template'),
            ['email_template_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
