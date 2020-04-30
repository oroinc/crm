<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_26;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddRFMMetrics implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addColumn('rfm_recency', 'integer', ['notnull' => false]);
        $table->addColumn('rfm_frequency', 'integer', ['notnull' => false]);
        $table->addColumn('rfm_monetary', 'integer', ['notnull' => false]);
    }
}
