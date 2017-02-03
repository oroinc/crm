<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_45;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMagentoBundle implements Migration
{
    /**
     * Changes account_id to onDelete=CASCADE
     *
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeOnDeleteToCascade(
            $schema,
            [
                'orocrm_magento_customer' => ['account_id'],
            ]
        );
    }

    /**
     * @param Schema $schema
     * @param array $data
     * [
     *     table name => [column name, ...],
     *     ...
     * ]
     */
    protected function changeOnDeleteToCascade(Schema $schema, array $data)
    {
        foreach ($data as $tableName => $columns) {
            $table = $schema->getTable($tableName);
            foreach ($columns as $column) {
                $foreignKeys = $table->getForeignKeys();
                foreach ($foreignKeys as $foreignKey) {
                    $foreignKeyColumns = $foreignKey->getUnquotedLocalColumns();
                    if ($foreignKeyColumns === [$column]) {
                        if ($foreignKey->getOption('onDelete') !== 'CASCADE') {
                            $table->removeForeignKey($foreignKey->getName());
                            $table->addForeignKeyConstraint(
                                $foreignKey->getUnqualifiedForeignTableName(),
                                $foreignKeyColumns,
                                $foreignKey->getUnquotedForeignColumns(),
                                ['onDelete' => 'CASCADE', 'onUpdate' => $foreignKey->getOption('onUpdate')]
                            );
                        }

                        break;
                    }
                }
            }
        }
    }
}
