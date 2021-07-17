<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveBuisnessCustomerRelation implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeFromLeadTable($schema);
        $this->removeFromOpportunityTable($schema);
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function removeFromLeadTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->removeForeignKey('FK_73DB46339395C3F3');
        $table->dropIndex('IDX_73DB46339395C3F3');
        $table->dropColumn('customer_id');
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function removeFromOpportunityTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->removeForeignKey('FK_C0FE4AAC9395C3F3');
        $table->dropIndex('IDX_C0FE4AAC9395C3F3');
        $table->dropColumn('customer_id');
    }
}
