<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_7;

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
        $table->addIndex(['name', 'id'], 'orocrm_b2bcustomer_name_idx', []);
    }
}
