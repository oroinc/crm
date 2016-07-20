<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class AddLeadAddressTable implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addMigrationQueries($queries);
        self::createLeadAddressTable($schema);
    }
    
    /**
     * Create orocrm_sales_lead_address table
     *
     * @param Schema $schema
     */
    public static function createLeadAddressTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
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
        $table->addIndex(['owner_id'], 'IDX_LEAD_ADDRESS_OWNER', []);
        $table->addIndex(['country_code'], 'IDX_LEAD_ADDRESS_COUNTRY', []);
        $table->addIndex(['region_code'], 'IDX_LEAD_ADDRESS_REGION', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Migrate addresses to the new table. Mark address as primary
     *
     * @param QueryBag $queries
     */
    protected function addMigrationQueries(QueryBag $queries)
    {
        $sql = 'INSERT INTO orocrm_sales_lead_address(owner_id, region_code, country_code, is_primary, label, street,' .
               ' street2, city, postal_code, organization, region_text, name_prefix, first_name, middle_name,' .
               ' last_name, name_suffix, created, updated)' .
               'SELECT lead.id, addr.region_code, addr.country_code,' .
               ' \'1\', addr.label, addr.street, addr.street2, addr.city, addr.postal_code, addr.organization,' .
               ' addr.region_text, addr.name_prefix, addr.first_name, addr.middle_name, addr.last_name,' .
               ' addr.name_suffix, addr.created, addr.updated FROM oro_address as addr' .
               ' INNER JOIN orocrm_sales_lead as lead on lead.address_id = addr.id';

        $queries->addPostQuery(new SqlMigrationQuery($sql));
    }
}
