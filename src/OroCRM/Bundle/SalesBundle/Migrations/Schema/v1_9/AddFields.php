<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddFields implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmLeadTable($schema);
        self::orocrmOpportunityTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmLeadTable(Schema $schema)
    {
        $leadTable = $schema->getTable('orocrm_sales_lead');

        $leadTable->addColumn('customer_id', 'integer', ['notnull' => false]);
        $leadTable->addIndex(['customer_id'], 'IDX_73DB46339395C3F3', []);

        $leadTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmOpportunityTable(Schema $schema)
    {
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');

        $opportunityTable->addColumn('customer_id', 'integer', ['notnull' => false]);
        $opportunityTable->addIndex(['customer_id'], 'IDX_C0FE4AAC9395C3F3', []);

        $opportunityTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
