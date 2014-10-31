<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

use OroCRM\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;

class OroCRMSalesBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer');
        $table->addColumn('lifetime', 'money', ['notnull' => false]);

        $sql = <<<SQL
                UPDATE
                    orocrm_sales_b2bcustomer c
                SET
                    c.lifetime = (
                      SELECT
                        SUM(o.close_revenue) lifetime
                      FROM
                        orocrm_sales_opportunity o
                      WHERE o.status_name = '%s' AND o.customer_id = c.id
                      GROUP BY o.customer_id
                )
SQL;
        $queries->addPostQuery(new SqlMigrationQuery(sprintf($sql, B2bCustomerRepository::VALUABLE_STATUS)));
    }
}
