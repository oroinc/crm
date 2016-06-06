<?php


namespace Oro\CRMCallBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;

class FillActivityAssociationTables implements
    Migration,
    OrderedMigrationInterface,
    ExtendExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

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
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** if CallBundle isn't installed  do nothing **/
        if (!$schema->hasTable('orocrm_call')) {
            return;
        }

        $this->fillActivityTables($queries);
        $this->fillActivityListTables($queries);

        // Remove orocrm_magento_cart_calls
        $table = $schema->getTable('orocrm_magento_cart_calls');
        $table->removeForeignKey('FK_83A847751AD5CDBF');
        $table->removeForeignKey('FK_83A8477550A89B2C');
        $schema->dropTable('orocrm_magento_cart_calls');

        // Remove orocrm_magento_order_calls
        $table = $schema->getTable('orocrm_magento_order_calls');
        $table->removeForeignKey('FK_A885A3450A89B2C');
        $table->removeForeignKey('FK_A885A348D9F6D38');
        $schema->dropTable('orocrm_magento_order_calls');
    }

    /**
     * @param QueryBag $queries
     */
    protected function fillActivityTables(QueryBag $queries)
    {
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
    protected function fillActivityListTables(QueryBag $queries)
    {
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
}