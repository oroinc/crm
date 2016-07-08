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

class UpdateContactUsDependencySchema implements
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
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::fillActivityTables($queries, $schema, $this->activityExtension);
        self::fillActivityListTables($queries, $schema, $this->activityListExtension, $this->activityExtension);

        // Remove orocrm_contactus_request_calls
        self::deleteContactUsRequestCallsTable($schema);
    }

    public static function deleteContactUsRequestCallsTable(Schema $schema)
    {
        /**If  table orocrm_contactus_request_calls does't exists do nothing **/
        if(!$schema->hasTable('orocrm_contactus_request_calls')) {
            return;
        }

        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->removeForeignKey('FK_6F7A50CE427EB8A5');
        $table->removeForeignKey('FK_6F7A50CE50A89B2C');
        $schema->dropTable('orocrm_contactus_request_calls');
    }

    /**
     * @param QueryBag $queries
     */
    public static function fillActivityTables(QueryBag $queries, Schema $schema, ActivityExtension $activityExtension)
    {
        /**If  table orocrm_contactus_request_calls does't exists do nothing **/
        if (!$schema->hasTable('orocrm_contactus_request_calls')) {
            return;
        }

        $queries->addPreQuery(
            new SqlMigrationQuery(
                self::getFillContactRequestCallActivityQuery($activityExtension)
            )
        );
    }

    /**
     * @param QueryBag $queries
     */
    public static function fillActivityListTables(
        QueryBag $queries,
        Schema $schema,
        ActivityListExtension $activityListExtension,
        ActivityExtension $activityExtension
    )
    {

        /**If  table orocrm_contactus_request_calls does't exists do nothing **/
        if (!$schema->hasTable('orocrm_contactus_request_calls')) {
            return;
        }

        $queries->addPostQuery(
            new ParametrizedSqlMigrationQuery(
                self::getFillContactRequestCallActivityListQuery($activityListExtension, $activityExtension),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
            )
        );
    }

    /**
     * @return string
     */
    protected static function getFillContactRequestCallActivityQuery(ActivityExtension $activityExtension)
    {
        $sql = 'INSERT INTO %s (call_id, contactrequest_id)' .
            ' SELECT call_id, request_id' .
            ' FROM orocrm_contactus_request_calls';

        return sprintf(
            $sql,
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_contactus_request')
        );
    }

    /**
     * @return string
     */
    protected static function getFillContactRequestCallActivityListQuery(
        ActivityListExtension $activityListExtension,
        ActivityExtension $activityExtension
    )
    {
        $sql = 'INSERT INTO %s (activitylist_id, contactrequest_id)' .
            ' SELECT al.id, rel.contactrequest_id' .
            ' FROM oro_activity_list al' .
            ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
            ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $activityListExtension->getAssociationTableName('orocrm_contactus_request'),
            $activityExtension->getAssociationTableName('orocrm_call', 'orocrm_contactus_request')
        );
    }
}
