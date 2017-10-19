<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOriginIdToOrder implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table  = $schema->getTable('orocrm_magento_order');

        if (!$table->hasColumn('origin_id')) {
            $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
            $table->addUniqueIndex(['origin_id', 'channel_id'], 'unq_origin_id_channel_id');
        }
    }
}
