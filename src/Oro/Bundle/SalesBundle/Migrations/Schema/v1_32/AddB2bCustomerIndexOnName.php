<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_32;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddB2bCustomerIndexOnName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer');
        if (!$table->hasIndex('orocrm_b2bcustomer_name_idx')) {
            $table->addIndex(['name', 'id'], 'orocrm_b2bcustomer_name_idx', []);
        }
    }
}
