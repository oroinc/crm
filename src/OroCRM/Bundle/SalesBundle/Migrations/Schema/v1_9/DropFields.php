<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropFields implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 3;
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
        $leadTable->removeForeignKey('FK_73DB46339B6B5FBA');
        $leadTable->dropIndex('IDX_73DB46339B6B5FBA');
        $leadTable->dropColumn('account_id');
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmOpportunityTable(Schema $schema)
    {
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable->removeForeignKey('FK_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropIndex('IDX_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropColumn('account_id');
    }
}
