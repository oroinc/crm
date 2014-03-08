<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle extends Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_sales_opportunity_close_reason', 'orocrm_sales_opport_close_rsn')
        );
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_sales_opportunity_status', 'orocrm_sales_opport_status')
        );
    }
}
