<?php
namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addColumn('lifetime', 'money', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
    }
}
