<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_41;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddSyncDates implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order');
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);

        $table = $schema->getTable('orocrm_magento_customer');
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);

        $table = $schema->getTable('orocrm_magento_cart');
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
    }
}
