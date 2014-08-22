<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMAccountBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');

        $table->dropColumn('extend_website');
        $table->dropColumn('extend_employees');
        $table->dropColumn('extend_ownership');
        $table->dropColumn('extend_ticker_symbol');
        $table->dropColumn('extend_rating');
        $table->dropColumn('extend_description');

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
                      WHERE class_name = 'OroCRM\\\\Bundle\\\\AccountBundle\\\\Entity\\\\Account'
                    )
                ;
DQL
            );
        }
    }
}
