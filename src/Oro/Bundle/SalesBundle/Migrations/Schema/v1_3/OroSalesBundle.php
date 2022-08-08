<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $leadTable = $schema->getTable('orocrm_sales_lead');
        $leadTable->addIndex(array('createdAt'), 'lead_created_idx');

        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable->getColumn('customer_need')->setType(Type::getType(Types::TEXT))->setLength(null);
        $opportunityTable->getColumn('proposed_solution')->setType(Type::getType(Types::TEXT))->setLength(null);
        $opportunityTable->addIndex(array('created_at'), 'opportunity_created_idx');
    }
}
