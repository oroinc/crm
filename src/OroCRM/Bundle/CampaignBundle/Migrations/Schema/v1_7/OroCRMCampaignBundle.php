<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrocrmCampaignTable($schema);

        $this->createOrocrmCampaignTeSummaryTable($schema);
        $this->addOrocrmCampaignTeSummaryForeignKeys($schema);
    }

    /**
     * Create orocrm_campaign table
     *
     * @param Schema $schema
     */
    protected function updateOrocrmCampaignTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign');
        $table->addColumn('report_refresh_date', 'date', ['notnull' => false]);
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
}
