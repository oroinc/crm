<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_10;

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
        $this->modifyOrocrmAccountTable($schema, $queries);
        $queries->addQuery(new UpdateExtendedFieldQuery());
    }
    // @codingStandardsIgnoreStart

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
                WHERE class_name = 'Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Lead'
            );
DQL
            );
        }
    }

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
                WHERE class_name = 'Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\Opportunity'
            );
DQL
            );
        }
    }

    protected function modifyOrocrmAccountTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');

        $table->dropColumn('extend_website');
        $table->dropColumn('extend_employees');
        $table->dropColumn('extend_ownership');
        $table->dropColumn('extend_ticker_symbol');
        $table->dropColumn('extend_rating');

        $table->removeForeignKey('FK_7166D3714D4CFF2B');
        $table->dropIndex('IDX_7166D3714D4CFF2B');
        $table->dropColumn('shipping_address_id');

        $table->removeForeignKey('FK_7166D37179D0C0E4');
        $table->dropIndex('IDX_7166D37179D0C0E4');
        $table->dropColumn('billing_address_id');

        if ($schema->hasTable('oro_entity_config_index_value') && $schema->hasTable('oro_entity_config_field')) {
            $queries->addPostQuery(
                <<<DQL
             DELETE FROM oro_entity_config_field
                   WHERE
                    field_name IN (
                        'extend_website',
                        'extend_employees',
                        'extend_ownership',
                        'extend_ticker_symbol',
                        'extend_rating',
                        'shippingAddress',
                        'billingAddress'
                    )
                    AND entity_id IN (
                      SELECT id
                      FROM oro_entity_config
                      WHERE class_name = 'Oro\\\\Bundle\\\\AccountBundle\\\\Entity\\\\Account'
                    )
                ;
DQL
            );
        }
    }
}
// @codingStandardsIgnoreEnd
