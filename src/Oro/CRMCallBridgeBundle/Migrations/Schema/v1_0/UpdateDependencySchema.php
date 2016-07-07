<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;

use \Symfony\Component\DependencyInjection\ContainerAwareTrait;
use \Symfony\Component\DependencyInjection\ContainerAwareInterface;

class UpdateDependencySchema implements
    Migration,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var ActivityExtension */
    protected $activityExtension;

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
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($this->container->hasParameter('installed')
            && $this->container->getParameter('installed')) {
                $this->addActivityAssociationsForCallBundle($schema, $queries);
        }

    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    protected function addActivityAssociationsForCallBundle(Schema $schema, QueryBag $queries)
    {

        $this->fillActivityListTables($queries, $schema);
        $this->fillActivityTables($queries, $schema);

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
     * @param string[] $tableNames
     * @param Schema $schema
     * @return bool
     */
    protected function checkIfTablesExists(array $tableNames, Schema $schema)
    {
        foreach ($tableNames as $tableName) {
            if (!$schema->hasTable($tableName)) {
                return false;
            }
        }

        return true;
    }
}
