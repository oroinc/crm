<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_43;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerLifetimeField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('orocrm_sales_b2bcustomer')) {
            $this->modifyLifetimeFields($schema);
        }
    }

    protected function modifyLifetimeFields(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer');

        if (!$table->hasColumn('lifetime')) {
            return;
        }

        $table->getColumn('lifetime')
            ->setOptions([OroOptions::KEY => ['dataaudit' => ['auditable' => false]]]);
    }
}
