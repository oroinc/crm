<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_3;

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
        /** Tables generation **/
        $this->createOrocrmCampaignEmailTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmCampaignEmailForeignKeys($schema);
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
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('marketing_list_id', 'integer', ['notnull' => false]);
        $table->addColumn('campaign_id', 'integer', []);
        $table->addColumn('is_sent', 'boolean', []);
        $table->addColumn('schedule', 'string', ['length' => 255]);
        $table->addColumn('scheduled_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('template_id', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['marketing_list_id'], 'idx_6cd4c1e196434d04', []);
        $table->addIndex(['campaign_id'], 'idx_6cd4c1e1f639f774', []);
        $table->addIndex(['owner_id'], 'idx_6cd4c1e17e3c61f9', []);
        $table->setPrimaryKey(['id']);
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
            $schema->getTable('oro_user'),
            ['owner_id'],
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
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
