<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendNameGeneratorAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Provides an ability to create customer associations.
 */
class CustomerExtension implements ExtendExtensionAwareInterface, NameGeneratorAwareInterface
{
    use ExtendExtensionAwareTrait;
    use ExtendNameGeneratorAwareTrait;

    const CUSTOMER_TABLE_NAME = 'orocrm_sales_customer';

    /**
     * Adds the association between the target customer table and the customer table
     *
     * @param Schema $schema
     * @param string $targetTableName  Target entity table name
     * @param string $targetColumnName A column name is used to show related entity
     */
    public function addCustomerAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null
    ) {
        $table   = $schema->getTable(self::CUSTOMER_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);
        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
            $targetColumnName  = reset($primaryKeyColumns);
        }
        $options = new OroOptions();
        $options->set('customer', 'enabled', true);
        $targetTable->addOption(OroOptions::KEY, $options);
        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            CustomerScope::ASSOCIATION_KIND
        );
        $this->extendExtension->addManyToOneRelation(
            $schema,
            $table,
            $associationName,
            $targetTable,
            $targetColumnName,
            [
                'importexport' => [
                    'full' => true,
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE,
                ],
                'extend' => [
                    'on_delete' => 'SET NULL'
                ]
            ]
        );
    }
}
