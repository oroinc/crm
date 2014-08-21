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

        $leadTable->addColumn('b2bcustomer_id', 'integer', ['notnull' => false]);
        $leadTable->addIndex(['b2bcustomer_id']);

        $leadTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['b2bcustomer_id'],
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

        $opportunityTable->addColumn('b2bcustomer_id', 'integer', ['notnull' => false]);
        $opportunityTable->addIndex(['b2bcustomer_id']);

        $opportunityTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['b2bcustomer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
