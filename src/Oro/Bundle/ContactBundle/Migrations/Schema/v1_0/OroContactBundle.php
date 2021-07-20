<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OroContactBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmContactTable($schema);
        self::orocrmContactAddressTable($schema);
        self::orocrmContactAddressToAddressTypeTable($schema);
        self::orocrmContactEmailTable($schema);
        self::orocrmContactGroupTable($schema);
        self::orocrmContactMethodTable($schema);
        self::orocrmContactPhoneTable($schema);
        self::orocrmContactSourceTable($schema);
        self::orocrmContactToContactGroupTable($schema);

        self::orocrmContactForeignKeys($schema);
        self::orocrmContactAddressForeignKeys($schema);
        self::orocrmContactAddressToAddressTypeForeignKeys($schema);
        self::orocrmContactEmailForeignKeys($schema);
        self::orocrmContactGroupForeignKeys($schema);
        self::orocrmContactPhoneForeignKeys($schema);
        self::orocrmContactToContactGroupForeignKeys($schema);
        self::oroEmailAddressForeignKeys($schema);
    }

    /**
     * Generate table oro_contact
     */
    public static function orocrmContactTable(Schema $schema)
    {
        /** Generate table oro_contact **/
        $table = $schema->createTable('orocrm_contact');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('updated_by_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('assigned_to_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('method_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('source_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('created_by_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('reports_to_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('gender', 'string', ['notnull' => false, 'length' => 8]);
        $table->addColumn('birthday', 'datetime', ['notnull' => false]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('job_title', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('fax', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('skype', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('twitter', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('facebook', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('google_plus', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('linkedin', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['source_name'], 'IDX_403263ED5FA9FB05', []);
        $table->addIndex(['method_name'], 'IDX_403263ED42F70470', []);
        $table->addIndex(['user_owner_id'], 'IDX_403263ED9EB185F9', []);
        $table->addIndex(['assigned_to_user_id'], 'IDX_403263ED11578D11', []);
        $table->addIndex(['reports_to_contact_id'], 'IDX_403263EDF27EBC1E', []);
        $table->addIndex(['created_by_user_id'], 'IDX_403263ED7D182D95', []);
        $table->addIndex(['updated_by_user_id'], 'IDX_403263ED2793CC5E', []);
        /** End of generate table oro_contact **/
    }

    /**
     * Generate table oro_contact_address
     */
    public static function orocrmContactAddressTable(Schema $schema)
    {
        /** Generate table oro_contact_address **/
        $table = $schema->createTable('orocrm_contact_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime', []);
        $table->addColumn('updated', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_CACC16DB7E3C61F9', []);
        $table->addIndex(['country_code'], 'IDX_CACC16DBF026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_CACC16DBAEB327AF', []);
        /** End of generate table oro_contact_address **/
    }

    /**
     * Generate table oro_contact_address_to_address_type
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactAddressToAddressTypeTable(Schema $schema, $tableName = null)
    {
        /** Generate table orocrm_contact_address_to_address_type **/
        $table = $schema->createTable($tableName ?: 'orocrm_contact_address_to_address_type');
        $table->addColumn('contact_address_id', 'integer', []);
        $table->addColumn('type_name', 'string', ['length' => 16]);
        $table->setPrimaryKey(['contact_address_id', 'type_name']);
        $table->addIndex(['contact_address_id'], 'IDX_3FBCDDC6320EF6E2', []);
        $table->addIndex(['type_name'], 'IDX_3FBCDDC6892CBB0E', []);
        /** End of generate table oro_contact_address_to_address_type **/
    }

    /**
     * Generate table oro_contact_email
     */
    public static function orocrmContactEmailTable(Schema $schema)
    {
        /** Generate table oro_contact_email **/
        $table = $schema->createTable('orocrm_contact_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_335A28C37E3C61F9', []);
        $table->addIndex(['email', 'is_primary'], 'primary_email_idx', []);
        /** End of generate table oro_contact_email **/
    }

    /**
     * Generate table oro_contact_group
     */
    public static function orocrmContactGroupTable(Schema $schema)
    {
        /** Generate table oro_contact_group **/
        $table = $schema->createTable('orocrm_contact_group');
        $table->addColumn('id', 'smallint', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('label', 'string', ['length' => 30]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['label'], 'UNIQ_B9081072EA750E8');
        $table->addIndex(['user_owner_id'], 'IDX_B90810729EB185F9', []);
        /** End of generate table oro_contact_group **/
    }

    /**
     * Generate table oro_contact_method
     */
    public static function orocrmContactMethodTable(Schema $schema)
    {
        /** Generate table oro_contact_method **/
        $table = $schema->createTable('orocrm_contact_method');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_B88D41BEA750E8');
        /** End of generate table oro_contact_method **/
    }

    /**
     * Generate table oro_contact_phone
     */
    public static function orocrmContactPhoneTable(Schema $schema)
    {
        /** Generate table oro_contact_phone **/
        $table = $schema->createTable('orocrm_contact_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_9087C36A7E3C61F9', []);
        $table->addIndex(['phone', 'is_primary'], 'primary_phone_idx', []);
        /** End of generate table oro_contact_phone **/
    }

    /**
     * Generate table oro_contact_source
     */
    public static function orocrmContactSourceTable(Schema $schema)
    {
        /** Generate table oro_contact_source **/
        $table = $schema->createTable('orocrm_contact_source');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_A5B9108EA750E8');
        /** End of generate table oro_contact_source **/
    }

    /**
     * Generate table oro_contact_to_contact_group
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactToContactGroupTable(Schema $schema, $tableName = null)
    {
        /** Generate table orocrm_contact_to_contact_group **/
        $table = $schema->createTable($tableName ?: 'orocrm_contact_to_contact_group');
        $table->addColumn('contact_id', 'integer', []);
        $table->addColumn('contact_group_id', 'smallint', []);
        $table->setPrimaryKey(['contact_id', 'contact_group_id']);
        $table->addIndex(['contact_id'], 'IDX_885CCB12E7A1254A', []);
        $table->addIndex(['contact_group_id'], 'IDX_885CCB12647145D0', []);
        /** End of generate table oro_contact_to_contact_group **/
    }

    /**
     * Generate foreign keys for table oro_contact
     */
    public static function orocrmContactForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contact **/
        $table = $schema->getTable('orocrm_contact');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['updated_by_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assigned_to_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_method'),
            ['method_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_source'),
            ['source_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['created_by_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['reports_to_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact **/
    }

    /**
     * Generate foreign keys for table oro_contact_address
     */
    public static function orocrmContactAddressForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contact_address **/
        $table = $schema->getTable('orocrm_contact_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_address **/
    }

    /**
     * Generate foreign keys for table oro_contact_address_to_address_type
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactAddressToAddressTypeForeignKeys(Schema $schema, $tableName = null)
    {
        /** Generate foreign keys for table orocrm_contact_address_to_address_type **/
        $table = $schema->getTable($tableName ?: 'orocrm_contact_address_to_address_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_address'),
            ['contact_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_address_to_address_type **/
    }

    /**
     * Generate foreign keys for table oro_contact_email
     */
    public static function orocrmContactEmailForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contact_email **/
        $table = $schema->getTable('orocrm_contact_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_email **/
    }

    /**
     * Generate foreign keys for table oro_contact_group
     */
    public static function orocrmContactGroupForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contact_group **/
        $table = $schema->getTable('orocrm_contact_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_group **/
    }

    /**
     * Generate foreign keys for table oro_contact_phone
     */
    public static function orocrmContactPhoneForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_contact_phone **/
        $table = $schema->getTable('orocrm_contact_phone');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['owner_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_phone **/
    }

    /**
     * Generate foreign keys for table oro_contact_to_contact_group
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmContactToContactGroupForeignKeys(Schema $schema, $tableName = null)
    {
        /** Generate foreign keys for table orocrm_contact_to_contact_group **/
        $table = $schema->getTable($tableName ?: 'orocrm_contact_to_contact_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_group'),
            ['contact_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_contact_to_contact_group **/
    }

    /**
     * Generate foreign keys for table oro_email_address
     */
    public static function oroEmailAddressForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table oro_email_address **/
        $table = $schema->getTable('oro_email_address');
        $table->addColumn('owner_contact_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_contact_id'], 'IDX_FC9DBBC5B5CBBC0F', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['owner_contact_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table oro_email_address **/
    }
}
