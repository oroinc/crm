<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerPassword implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerTable = $schema->getTable('orocrm_magento_customer');
        $customerTable->addColumn('password', 'string', ['notnull' => false, 'length' => 32]);
    }
}
