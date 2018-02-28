<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroAccountBundle implements Migration
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // @codingStandardsIgnoreStart

        /** Generate table oro_account **/
        $table = $schema->createTable('orocrm_account');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('default_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->addColumn(
            'extend_description',
            'text',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'extend_website',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'extend_employees',
            'integer',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'extend_ownership',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'extend_ticker_symbol',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'extend_rating',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'IDX_7166D3719EB185F9', []);
        $table->addIndex(['shipping_address_id'], 'IDX_7166D3714D4CFF2B', []);
        $table->addIndex(['billing_address_id'], 'IDX_7166D37179D0C0E4', []);
        $table->addIndex(['default_contact_id'], 'IDX_7166D371AF827129', []);
        $table->addIndex(['name'], 'account_name_idx', []);
        /** End of generate table oro_account **/

        /** Generate table oro_account_to_contact **/
        $table = $schema->createTable('orocrm_account_to_contact');
        $table->addColumn('account_id', 'integer', []);
        $table->addColumn('contact_id', 'integer', []);
        $table->setPrimaryKey(['account_id', 'contact_id']);
        $table->addIndex(['account_id'], 'IDX_65B8FBEC9B6B5FBA', []);
        $table->addIndex(['contact_id'], 'IDX_65B8FBECE7A1254A', []);
        /** End of generate table oro_account_to_contact **/

        /** Generate foreign keys for table oro_account **/
        $table = $schema->getTable('orocrm_account');
        $table->addForeignKeyConstraint($schema->getTable('orocrm_contact'), ['default_contact_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('oro_address'), ['shipping_address_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('oro_address'), ['billing_address_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('oro_user'), ['user_owner_id'], ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]);
        /** End of generate foreign keys for table oro_account **/

        /** Generate foreign keys for table oro_account_to_contact **/
        $table = $schema->getTable('orocrm_account_to_contact');
        $table->addForeignKeyConstraint($schema->getTable('orocrm_contact'), ['contact_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => null]);
        $table->addForeignKeyConstraint($schema->getTable('orocrm_account'), ['account_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => null]);
        /** End of generate foreign keys for table oro_account_to_contact **/
        // @codingStandardsIgnoreEnd
    }
}
