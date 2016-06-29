<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMCallBridgeBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface,
    NoteExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        /** if CallBundle is installed  do nothing **/
        if ($schema->hasTable('orocrm_call')) {
            return;
        }

        /** Tables generation **/
        $this->createOrocrmContactusRequestCallsTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmContactusRequestCallsForeignKeys($schema);


        $this->fillActivityTables($queries, $schema);
        $this->fillActivityListTables($queries, $schema);

        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_b2bcustomer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_contactus_request');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_customer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_order');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_cart');

        $associationTableName = $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_case');
        }
    }

    /**
     * Create orocrm_contactus_request_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmContactusRequestCallsTable(Schema $schema)
    {
        /**If table orocrm_contactus_request_calls do nothing **/
        if ($schema->hasTable('orocrm_contactus_request_calls')) {
            return;
        }

        $table = $schema->createTable('orocrm_contactus_request_calls');
        $table->addColumn('request_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->setPrimaryKey(['request_id', 'call_id']);
        $table->addIndex(['request_id'], 'IDX_6F7A50CE427EB8A5', []);
        $table->addIndex(['call_id'], 'IDX_6F7A50CE50A89B2C', []);
    }

    /**
     * Add orocrm_contactus_request_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmContactusRequestCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_request'),
            ['request_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param QueryBag $queries
     */
    protected function fillActivityTables(QueryBag $queries, Schema $schema)
    {
        $tables = [
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'),
            'orocrm_magento_cart_calls',
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order'),
            'orocrm_magento_order_calls'

        ];

        /**If some tables are not installed, do nothing **/
        if (!$this->checkIfTablesExists($tables, $schema)) {
            return;
        }

        $queries->addPreQuery(
            new SqlMigrationQuery(
                [
                    $this->getFillCartCallActivityQuery(),
                    $this->getFillOrderCallActivityQuery()
                ]
            )
        );
    }

    /**
     * @param QueryBag $queries
     */
    protected function fillActivityListTables(QueryBag $queries, Schema $schema)
    {
        $tables = [
            $this->activityListExtension->getAssociationTableName('orocrm_magento_cart'),
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'),
            $this->activityListExtension->getAssociationTableName('orocrm_magento_order'),
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order')
        ];

        /**If some tables are not installed, do nothing **/
        if (!$this->checkIfTablesExists($tables, $schema)) {
            return;
        }

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillCartCallActivityListQuery(),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillOrderCallActivityListQuery(),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );
    }

    /**
     * @return string
     */
    protected function getFillCartCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, cart_id)' .
            ' SELECT call_id, cart_id' .
            ' FROM orocrm_magento_cart_calls';

        return sprintf($sql, $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'));
    }

    /**
     * @return string
     */
    protected function getFillOrderCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, order_id)' .
            ' SELECT call_id, order_id' .
            ' FROM orocrm_magento_order_calls';

        return sprintf($sql, $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order'));
    }


    /**
     * @return string
     */
    protected function getFillCartCallActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, cart_id)' .
            ' SELECT al.id, rel.cart_id' .
            ' FROM oro_activity_list al' .
            ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
            ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->activityListExtension->getAssociationTableName('orocrm_magento_cart'),
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart')
        );
    }


    /**
     * @return string
     */
    protected function getFillOrderCallActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, order_id)' .
            ' SELECT al.id, rel.order_id' .
            ' FROM oro_activity_list al' .
            ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
            ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->activityListExtension->getAssociationTableName('orocrm_magento_order'),
            $this->activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order')
        );
    }

    /**
     * Check if some tables in database
     * are exists ans return boolean value
     *
     * @param array $tableNames
     * @param Schema $schema
     * @return bool
     */
    private function checkIfTablesExists(array $tableNames, Schema $schema)
    {
        foreach ($tableNames as $tableName) {
            if (!$schema->hasTable($tableName)) {
                return false;
            }
        }

        return true;
    }
}
