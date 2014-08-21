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
        return 3;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmLeadTable($schema, $queries);
        self::orocrmOpportunityTable($schema, $queries);
    }

    /**
     * @param Schema   $schema
     * @param QueryBag $queries
     */
    protected static function orocrmLeadTable(Schema $schema, QueryBag $queries)
    {
        $leadTable = $schema->getTable('orocrm_sales_lead');
        $leadTable->removeForeignKey('FK_73DB46339B6B5FBA');
        $leadTable->dropIndex('IDX_73DB46339B6B5FBA');
        $leadTable->dropColumn('account_id');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
                'DELETE FROM oro_entity_config_index_value
                 WHERE entity_id IS NULL AND field_id IN (
                     SELECT oecf.id FROM oro_entity_config_field AS oecf
                     WHERE (oecf.field_name = \'account_id\')
                     AND oecf.entity_id = (
                         SELECT oec.id
                         FROM oro_entity_config AS oec
                         WHERE oec.class_name = \'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Lead\'
                     )
                 );

                 DELETE FROM oro_entity_config_field
                   WHERE field_name IN (\'account_id\')
                    AND entity_id IN (
                        SELECT id
                        FROM oro_entity_config
                        WHERE class_name = \'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Lead\'
                    )'
            );
        }
    }

    /**
     * @param Schema   $schema
     * @param QueryBag $queries
     */
    protected static function orocrmOpportunityTable(Schema $schema, QueryBag $queries)
    {
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable->removeForeignKey('FK_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropIndex('IDX_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropColumn('account_id');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
                'DELETE FROM oro_entity_config_index_value
                 WHERE entity_id IS NULL AND field_id IN (
                     SELECT oecf.id FROM oro_entity_config_field AS oecf
                     WHERE (oecf.field_name = \'account_id\')
                     AND oecf.entity_id = (
                         SELECT oec.id
                         FROM oro_entity_config AS oec
                         WHERE oec.class_name = \'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Opportunity\'
                     )
                 );

                 DELETE FROM oro_entity_config_field
                   WHERE field_name IN (\'account_id\')
                    AND entity_id IN (
                        SELECT id
                        FROM oro_entity_config
                        WHERE class_name = \'OroCRM\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Opportunity\'
                    )'
            );
        }
    }
}
