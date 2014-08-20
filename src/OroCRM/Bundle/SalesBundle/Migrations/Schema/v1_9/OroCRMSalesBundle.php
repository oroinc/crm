<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration, ExtendExtensionAwareInterface
{

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmB2BCustomerTable($schema);

        self::orocrmLeadTable($schema);
        self::orocrmOpportunityTable($schema);

        self::setForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmLeadTable(Schema $schema)
    {
        $leadTable = $schema->getTable('orocrm_sales_lead');
        /*$leadTable->removeForeignKey('FK_73DB46339B6B5FBA');
        $leadTable->dropIndex('IDX_73DB46339B6B5FBA');
        $leadTable->dropColumn('account_id');*/

        $leadTable->addColumn('b2bcustomer_id', 'integer', ['notnull' => false]);
        $leadTable->addIndex(['b2bcustomer_id']);

        $leadTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2b_customer'),
            ['b2bcustomer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmOpportunityTable(Schema $schema)
    {
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        /*$opportunityTable->removeForeignKey('FK_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropIndex('IDX_C0FE4AAC9B6B5FBA');
        $opportunityTable->dropColumn('account_id');*/

        $opportunityTable->addColumn('b2bcustomer_id', 'integer', ['notnull' => false]);
        $opportunityTable->addIndex(['b2bcustomer_id']);

        $opportunityTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2b_customer'),
            ['b2bcustomer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected static function orocrmB2BCustomerTable(Schema $schema)
    {
        $b2bCustomerTable = $schema->createTable('orocrm_sales_b2b_customer');
        $b2bCustomerTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $b2bCustomerTable->addColumn('account_id', 'integer', ['notnull' => false]);
        $b2bCustomerTable->setPrimaryKey(['id']);
        $b2bCustomerTable->addIndex(['account_id'], 'IDX_94CC12929B6B5FBA', []);
    }

    /**
     * @param Schema $schema
     */
    protected static function setForeignKeys(Schema $schema)
    {
        $b2bCustomerTable = $schema->getTable('orocrm_sales_b2b_customer');
        $b2bCustomerTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
