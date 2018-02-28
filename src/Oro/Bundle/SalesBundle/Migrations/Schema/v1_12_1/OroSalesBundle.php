<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_12_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;

class OroSalesBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $sql = <<<SQL
                UPDATE
                    orocrm_sales_b2bcustomer c
                SET
                    lifetime = (
                      SELECT
                        SUM(o.close_revenue) lifetime
                      FROM
                        orocrm_sales_opportunity o
                      WHERE o.status_name = '%s' AND o.customer_id = c.id
                )
SQL;
        $queries->addPostQuery(new SqlMigrationQuery(sprintf($sql, B2bCustomerRepository::VALUABLE_STATUS)));
    }
}
