<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $salesFunnelTable = $schema->getTable('orocrm_sales_funnel');
        $salesFunnelTable->dropColumn('name');
        $salesFunnelTable->addIndex(array('startDate'), 'sales_start_idx');

        $leadTable = $schema->getTable('orocrm_sales_lead');
        $leadTable->addIndex(array('createdAt'), 'lead_created_idx');

        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable->getColumn('customer_need')->setType(Type::getType(Type::TEXT))->setLength(null);
        $opportunityTable->getColumn('proposed_solution')->setType(Type::getType(Type::TEXT))->setLength(null);
        $opportunityTable->addIndex(array('created_at'), 'opportunity_created_idx');
    }
}
