<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_37;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityListBundle\Tools\ActivityListEntityConfigDumperExtension;

class FillActivityAssociationTables implements
    Migration,
    OrderedMigrationInterface,
    NameGeneratorAwareInterface,
    ExtendExtensionAwareInterface
{
    /** @var ExtendDbIdentifierNameGenerator */
    protected $nameGenerator;

    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
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
        $this->fillActivityTables($queries);
        $this->fillActivityListTables($queries);

        // Remove orocrm_magento_cart_emails
        $table = $schema->getTable('orocrm_magento_cart_emails');
        $table->removeForeignKey('FK_11B0F84B1AD5CDBF');
        $table->removeForeignKey('FK_11B0F84BA832C1C9');
        $schema->dropTable('orocrm_magento_cart_emails');

        // Remove orocrm_magento_cart_calls
        $table = $schema->getTable('orocrm_magento_cart_calls');
        $table->removeForeignKey('FK_83A847751AD5CDBF');
        $table->removeForeignKey('FK_83A8477550A89B2C');
        $schema->dropTable('orocrm_magento_cart_calls');

        // Remove orocrm_magento_order_emails
        $table = $schema->getTable('orocrm_magento_order_emails');
        $table->removeForeignKey('FK_10E2A9508D9F6D38');
        $table->removeForeignKey('FK_10E2A950A832C1C9');
        $schema->dropTable('orocrm_magento_order_emails');

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
                [$this->getFillCartEmailActivityQuery(), $this->getFillCartCallActivityQuery(
                ), $this->getFillOrderEmailActivityQuery(), $this->getFillOrderCallActivityQuery()

                ]
            )
        );
    }

    /**
     * @param QueryBag $queries
     */
    protected function fillActivityListTables(QueryBag $queries)
    {
        // Fill activitylists tables
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillCartEmailActivityListQuery(),
                ['class' => 'Oro\Bundle\EmailBundle\Entity\Email'],
                ['class' => Type::STRING]
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillCartCallActivityListQuery(),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );

        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillOrderEmailActivityListQuery(),
                ['class' => 'Oro\Bundle\EmailBundle\Entity\Email'],
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
    protected function getFillCartEmailActivityQuery()
    {
        $sql = 'INSERT INTO %s (email_id, cart_id)' .
               ' SELECT email_id, cart_id' .
               ' FROM orocrm_magento_cart_emails';

        return sprintf($sql, $this->getAssociationTableName('oro_email', 'orocrm_magento_cart'));
    }

    /**
     * @return string
     */
    protected function getFillCartCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, cart_id)' .
               ' SELECT call_id, cart_id' .
               ' FROM orocrm_magento_cart_calls';

        return sprintf($sql, $this->getAssociationTableName('orocrm_call', 'orocrm_magento_cart'));
    }

    /**
     * @return string
     */
    protected function getFillOrderEmailActivityQuery()
    {
        $sql = 'INSERT INTO %s (email_id, order_id)' .
               ' SELECT email_id, order_id' .
               ' FROM orocrm_magento_order_emails';

        return sprintf($sql, $this->getAssociationTableName('oro_email', 'orocrm_magento_order'));
    }

    /**
     * @return string
     */
    protected function getFillOrderCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, order_id)' .
               ' SELECT call_id, order_id' .
               ' FROM orocrm_magento_order_calls';

        return sprintf($sql, $this->getAssociationTableName('orocrm_call', 'orocrm_magento_order'));
    }

    /**
     * @return string
     */
    protected function getFillCartEmailActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, cart_id)' .
               ' SELECT al.id, rel.cart_id' .
               ' FROM oro_activity_list al' .
               ' JOIN %s rel ON rel.email_id = al.related_activity_id' .
               ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->getAssociationActivityListTableName('orocrm_magento_cart'),
            $this->getAssociationTableName('oro_email', 'orocrm_magento_cart')
        );
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
            $this->getAssociationActivityListTableName('orocrm_magento_cart'),
            $this->getAssociationTableName('orocrm_call', 'orocrm_magento_cart')
        );
    }

    /**
     * @return string
     */
    protected function getFillOrderEmailActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, order_id)' .
               ' SELECT al.id, rel.order_id' .
               ' FROM oro_activity_list al' .
               ' JOIN %s rel ON rel.email_id = al.related_activity_id' .
               ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->getAssociationActivityListTableName('orocrm_magento_order'),
            $this->getAssociationTableName('oro_email', 'orocrm_magento_order')
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
            $this->getAssociationActivityListTableName('orocrm_magento_order'),
            $this->getAssociationTableName('orocrm_call', 'orocrm_magento_order')
        );
    }

    /**
     * Gets a table name for many-to-many relation
     *
     * @param string $activityTableName Activity entity table name. It is owning side of the association.
     * @param string $targetTableName   Target entity table name.
     *
     * @return string
     */
    protected function getAssociationTableName($activityTableName, $targetTableName)
    {
        $sourceClassName = $this->extendExtension->getEntityClassByTableName($activityTableName);
        $targetClassName = $this->extendExtension->getEntityClassByTableName($targetTableName);

        $associationName = ExtendHelper::buildAssociationName(
            $targetClassName,
            ActivityScope::ASSOCIATION_KIND
        );

        return $this->nameGenerator->generateManyToManyJoinTableName(
            $sourceClassName,
            $associationName,
            $targetClassName
        );
    }

    /**
     * Gets an activity list table name for many-to-many relation
     *
     * @param string $targetTableName Target entity table name.
     *
     * @return string
     */
    protected function getAssociationActivityListTableName($targetTableName)
    {
        $targetClassName = $this->extendExtension->getEntityClassByTableName($targetTableName);

        $associationName = ExtendHelper::buildAssociationName(
            $targetClassName,
            ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND
        );

        return $this->nameGenerator->generateManyToManyJoinTableName(
            ActivityListEntityConfigDumperExtension::ENTITY_CLASS,
            $associationName,
            $targetClassName
        );
    }
}
