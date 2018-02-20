<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RenameMagentoCartStatusLabel implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $sql = 'UPDATE orocrm_magento_cart_status' .
            ' SET label = :label' .
            ' WHERE name = :name';

        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                $sql,
                ['label' => 'Converted to Opportunity', 'name' => 'converted_to_opportunity']
            )
        );
    }
}
