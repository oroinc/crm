<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_52;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddIsDisplayOrderNotes implements Migration
{
    /**
     * @param Schema   $schema
     * @param QueryBag $queries
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn(
            'is_display_order_notes',
            'boolean',
            [
                'notnull' => false,
                'default' => true
            ]
        );
    }
}
