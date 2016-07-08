<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;

class UpdateMagentoDependencySchema implements
    Migration,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    OrderedMigrationInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

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
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::fillActivityTables($queries, $schema, $this->activityExtension);
        self::fillActivityListTables($queries, $schema, $this->activityExtension, $this->activityListExtension);
    }

    /**
     * @param QueryBag $queries
     */
    public static function fillActivityTables(QueryBag $queries, Schema $schema, ActivityExtension $activityExtension)
    {
        $tables = [
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'),
            'orocrm_magento_cart_calls',
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order'),
            'orocrm_magento_order_calls'

        ];

        /**If some tables are not installed, do nothing **/
        if (!self::checkIfTablesExists($tables, $schema)) {
            return;
        }

        $queries->addPreQuery(
            new SqlMigrationQuery(
                [
                    self::getFillCartCallActivityQuery($activityExtension),
                    self::getFillOrderCallActivityQuery($activityExtension)
                ]
            )
        );
    }

    /**
     * @param QueryBag $queries
     */
    public static function fillActivityListTables(
        QueryBag $queries,
        Schema $schema,
        ActivityExtension $activityExtension,
        ActivityListExtension $activityListExtension
    ) {
        $tables = [
            $activityListExtension->getAssociationTableName('orocrm_magento_cart'),
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'),
            $activityListExtension->getAssociationTableName('orocrm_magento_order'),
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order')
        ];

        /**If some tables are not installed, do nothing **/
        if (!self::checkIfTablesExists($tables, $schema)) {
            return;
        }

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                self::getFillCartCallActivityListQuery($activityExtension, $activityListExtension),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                self::getFillOrderCallActivityListQuery($activityExtension, $activityListExtension),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );
    }

    /**
     * @return string
     */
    protected static function getFillCartCallActivityQuery(ActivityExtension $activityExtension)
    {
        $sql = 'INSERT INTO %s (call_id, cart_id)' .
            ' SELECT call_id, cart_id' .
            ' FROM orocrm_magento_cart_calls';

        return sprintf($sql, $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'));
    }

    /**
     * @return string
     */
    protected function getFillOrderCallActivityQuery(ActivityExtension $activityExtension)
    {
        $sql = 'INSERT INTO %s (call_id, order_id)' .
            ' SELECT call_id, order_id' .
            ' FROM orocrm_magento_order_calls';

        return sprintf($sql, $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order'));
    }


    /**
     * @return string
     */
    protected function getFillCartCallActivityListQuery(
        ActivityExtension $activityExtension,
        ActivityListExtension $activityListExtension
    ) {
        $sql = 'INSERT INTO %s (activitylist_id, cart_id)' .
            ' SELECT al.id, rel.cart_id' .
            ' FROM oro_activity_list al' .
            ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
            ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $activityListExtension->getAssociationTableName('orocrm_magento_cart'),
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_cart')
        );
    }


    /**
     * @return string
     */
    protected function getFillOrderCallActivityListQuery(
        ActivityExtension $activityExtension,
        ActivityListExtension $activityListExtension
    ) {
        $sql = 'INSERT INTO %s (activitylist_id, order_id)' .
            ' SELECT al.id, rel.order_id' .
            ' FROM oro_activity_list al' .
            ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
            ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $activityListExtension->getAssociationTableName('orocrm_magento_order'),
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_magento_order')
        );
    }

    /**
     * Check if some tables in database
     * are exists ans return boolean value
     *
     * @param string[] $tableNames
     * @param Schema $schema
     * @return bool
     */
    protected static function checkIfTablesExists(array $tableNames, Schema $schema)
    {
        foreach ($tableNames as $tableName) {
            if (!$schema->hasTable($tableName)) {
                return false;
            }
        }

        return true;
    }
}
