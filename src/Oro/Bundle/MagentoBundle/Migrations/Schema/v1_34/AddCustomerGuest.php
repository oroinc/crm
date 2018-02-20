<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_34;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerGuest implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addColumn('is_guest', 'boolean', ['notnull' => true, 'default' => false]);
    }
}
