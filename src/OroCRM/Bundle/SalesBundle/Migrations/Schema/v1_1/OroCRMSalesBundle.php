<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class OroCRMSalesBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "ALTER TABLE orocrm_sales_opportunity_close_reason RENAME TO orocrm_sales_opportunity_close;",
            "ALTER TABLE orocrm_sales_opportunity_status RENAME TO orocrm_sales_opportunity_stat;",
        ];
    }
}
