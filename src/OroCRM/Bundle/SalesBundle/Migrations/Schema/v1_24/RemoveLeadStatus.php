<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveLeadStatus implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $statusColumnName = 'status_name';

        $leadForeignKeyList = $table->getForeignKeys();
        foreach ($leadForeignKeyList as $foreignKey) {
            $foreingKeyColumns = $foreignKey->getUnquotedLocalColumns();
            if (in_array($statusColumnName, $foreingKeyColumns, true)) {
                $table->removeForeignKey($foreignKey->getName());
            }
        }

        $leadIndexList = $table->getIndexes();
        foreach ($leadIndexList as $index) {
            if ($index->hasColumnAtPosition($statusColumnName, 0)) {
                $table->dropIndex($index->getName());
            }
        }

        if ($table->hasColumn($statusColumnName)) {
            $table->dropColumn($statusColumnName);
        }
    }
}
