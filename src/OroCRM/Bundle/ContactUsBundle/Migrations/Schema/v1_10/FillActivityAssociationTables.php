<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_10;

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

        // Remove orocrm_contactus_req_emails
        $table = $schema->getTable('orocrm_contactus_req_emails');
        $table->removeForeignKey('FK_E494F7AE427EB8A5');
        $table->removeForeignKey('FK_E494F7AEA832C1C9');
        $schema->dropTable('orocrm_contactus_req_emails');

        // Remove orocrm_contactus_request_calls
        $table = $schema->getTable('orocrm_contactus_request_calls');
        $table->removeForeignKey('FK_6F7A50CE427EB8A5');
        $table->removeForeignKey('FK_6F7A50CE50A89B2C');
        $schema->dropTable('orocrm_contactus_request_calls');

    }

    /**
     * @param QueryBag $queries
     */
    protected function fillActivityTables(QueryBag $queries)
    {
        $queries->addPreQuery(
            new SqlMigrationQuery(
                [$this->getFillContactRequestEmailActivityQuery(), $this->getFillContactRequestCallActivityQuery()]
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
                $this->getFillContactRequestEmailActivityListQuery(),
                ['class' => 'Oro\Bundle\EmailBundle\Entity\Email'],
                ['class' => Type::STRING]
            )
        );
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                $this->getFillContactRequestCallActivityListQuery(),
                ['class' => 'OroCRM\Bundle\CallBundle\Entity\Call'],
                ['class' => Type::STRING]
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

        return sprintf($sql, $this->getAssociationTableName('oro_email', 'orocrm_contactus_request'));
    }

    /**
     * @return string
     */
    protected function getFillContactRequestCallActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, contactrequest_id)' .
               ' SELECT call_id, request_id' .
               ' FROM orocrm_contactus_request_calls';

        return sprintf($sql, $this->getAssociationTableName('orocrm_call', 'orocrm_contactus_request'));
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
            $this->getAssociationActivityListTableName('orocrm_contactus_request'),
            $this->getAssociationTableName('oro_email', 'orocrm_contactus_request')
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
            $this->getAssociationActivityListTableName('orocrm_contactus_request'),
            $this->getAssociationTableName('orocrm_call', 'orocrm_contactus_request')
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
