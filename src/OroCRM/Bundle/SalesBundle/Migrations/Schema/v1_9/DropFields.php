<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropFields implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 30;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->modifyOrocrmLeadTable($schema, $queries);
        $this->modifyOrocrmOpportunityTable($schema, $queries);
    }

    /**
     * @param Schema   $schema
     * @param QueryBag $queries
     */
    protected function modifyOrocrmLeadTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->removeForeignKey('FK_73DB46339B6B5FBA');
        $table->dropIndex('IDX_73DB46339B6B5FBA');
        $table->dropColumn('account_id');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
<<<DQL
            DELETE FROM oro_entity_config_field
            WHERE field_name = 'account'
            AND entity_id IN (
                SELECT id
                FROM oro_entity_config
                WHERE class_name = 'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Lead'
            );
DQL
            );
        }
    }

    /**
     * @param Schema   $schema
     * @param QueryBag $queries
     */
    protected function modifyOrocrmOpportunityTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->removeForeignKey('FK_C0FE4AAC9B6B5FBA');
        $table->dropIndex('IDX_C0FE4AAC9B6B5FBA');
        $table->dropColumn('account_id');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
<<<DQL
            DELETE FROM oro_entity_config_field
            WHERE field_name = 'account'
            AND entity_id IN (
                SELECT id
                FROM oro_entity_config
                WHERE class_name = 'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Opportunity'
            );
DQL
            );
        }
    }
}
