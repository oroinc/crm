<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class FillActivityAssociationTables implements
    Migration,
    OrderedMigrationInterface,
    ExtendExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;
    use ActivityExtensionAwareTrait;
    use ActivityListExtensionAwareTrait;

    #[\Override]
    public function getOrder()
    {
        return 2;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->fillActivityTables($queries);
        $this->fillActivityListTables($queries);

        // Remove oro_contactus_req_emails
        $table = $schema->getTable('orocrm_contactus_req_emails');
        if ($table->hasForeignKey('FK_E494F7AE427EB8A5')) {
            $table->removeForeignKey('FK_E494F7AE427EB8A5');
        }
        if ($table->hasForeignKey('FK_E494F7AEA832C1C9')) {
            $table->removeForeignKey('FK_E494F7AEA832C1C9');
        }
        // Delete foreign keys for oro_contactus_request_emails table,
        // that was renamed to oro_contactus_req_emails
        if ($table->hasForeignKey('FK_4DEF4058427EB8A5')) {
            $table->removeForeignKey('FK_4DEF4058427EB8A5');
        }
        if ($table->hasForeignKey('FK_4DEF4058A832C1C9')) {
            $table->removeForeignKey('FK_4DEF4058A832C1C9');
        }
        $schema->dropTable('orocrm_contactus_req_emails');

        // Remove oro_contactus_request_calls
        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->removeForeignKey('FK_6F7A50CE427EB8A5');
        $table->removeForeignKey('FK_6F7A50CE50A89B2C');
        $schema->dropTable('orocrm_contactus_request_calls');
    }

    protected function fillActivityTables(QueryBag $queries)
    {
        $queries->addPreQuery(
            new SqlMigrationQuery(
                [$this->getFillContactRequestEmailActivityQuery(), $this->getFillContactRequestCallActivityQuery()]
            )
        );
    }

    protected function fillActivityListTables(QueryBag $queries)
    {
        // Fill activitylists tables
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillContactRequestEmailActivityListQuery(),
                ['class' => 'Oro\Bundle\EmailBundle\Entity\Email'],
                ['class' => Types::STRING]
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillContactRequestCallActivityListQuery(),
                ['class' => 'Oro\Bundle\CallBundle\Entity\Call'],
                ['class' => Types::STRING]
            )
        );
    }

    /**
     * @return string
     */
    protected function getFillContactRequestEmailActivityQuery()
    {
        $sql = 'INSERT INTO %s (email_id, contactrequest_id)' .
               ' SELECT email_id, request_id' .
               ' FROM orocrm_contactus_req_emails';

        return sprintf(
            $sql,
            $this->activityExtension->getAssociationTableName('oro_email', 'orocrm_contactus_request')
        );
    }

    /**
     * @return string
     */
    protected function getFillContactRequestCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, contactrequest_id)' .
               ' SELECT call_id, request_id' .
               ' FROM orocrm_contactus_request_calls';

        return sprintf(
            $sql,
            $this->activityExtension->getAssociationTableName('oro_call', 'orocrm_contactus_request')
        );
    }

    /**
     * @return string
     */
    protected function getFillContactRequestEmailActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, contactrequest_id)' .
               ' SELECT al.id, rel.contactrequest_id' .
               ' FROM oro_activity_list al' .
               ' JOIN %s rel ON rel.email_id = al.related_activity_id' .
               ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->activityListExtension->getAssociationTableName('orocrm_contactus_request'),
            $this->activityExtension->getAssociationTableName('oro_email', 'orocrm_contactus_request')
        );
    }

    /**
     * @return string
     */
    protected function getFillContactRequestCallActivityListQuery()
    {
        $sql = 'INSERT INTO %s (activitylist_id, contactrequest_id)' .
               ' SELECT al.id, rel.contactrequest_id' .
               ' FROM oro_activity_list al' .
               ' JOIN %s rel ON rel.call_id = al.related_activity_id' .
               ' AND al.related_activity_class = :class';

        return sprintf(
            $sql,
            $this->activityListExtension->getAssociationTableName('orocrm_contactus_request'),
            $this->activityExtension->getAssociationTableName('oro_call', 'orocrm_contactus_request')
        );
    }
}
