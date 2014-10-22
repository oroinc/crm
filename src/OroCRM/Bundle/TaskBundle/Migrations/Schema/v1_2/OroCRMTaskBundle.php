<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

class OroCRMTaskBundle implements
    Migration,
    OrderedMigrationInterface,
    NameGeneratorAwareInterface,
    ExtendExtensionAwareInterface
{
    /** @var ExtendDbIdentifierNameGenerator */
    protected $nameGenerator;

    /** @var ExtendExtension */
    protected $extendExtension;

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
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery($this->getFillAccountActivityQuery());
        $queries->addPreQuery($this->getFillContactActivityQuery());

        $taskTable = $schema->getTable('orocrm_task');

        // relation with account
        $taskTable->removeForeignKey('FK_814DEE3F11A6570A');
        $taskTable->dropColumn('related_account_id');
        $queries->addPostQuery($this->getDropEntityConfigManyToOneRelationQuery('relatedAccount'));

        // relation with contact
        $taskTable->removeForeignKey('FK_814DEE3F6D6C2DFA');
        $taskTable->dropColumn('related_contact_id');
        $queries->addPostQuery($this->getDropEntityConfigManyToOneRelationQuery('relatedContact'));

        // fill empty updatedAt of orocrm_task
        $queries->addPreQuery('UPDATE orocrm_task SET updatedAt = createdAt WHERE updatedAt IS NULL');

        $taskTable = $schema->getTable('orocrm_task');

        // make updatedAt NOT NULL
        $taskTable->getColumn('updatedAt')->setOptions(['notnull' => true]);
    }

    /**
     * @return string
     */
    protected function getFillAccountActivityQuery()
    {
        $sql = 'INSERT INTO %s (task_id, account_id)'
            . ' SELECT id, related_account_id'
            . ' FROM orocrm_task'
            . ' WHERE related_account_id IS NOT NULL';

        return sprintf($sql, $this->getAssociationTableName('orocrm_account'));
    }

    /**
     * @return string
     */
    protected function getFillContactActivityQuery()
    {
        $sql = 'INSERT INTO %s (task_id, contact_id)'
            . ' SELECT id, related_contact_id'
            . ' FROM orocrm_task'
            . ' WHERE related_contact_id IS NOT NULL';

        return sprintf($sql, $this->getAssociationTableName('orocrm_contact'));
    }

    /**
     * @param string $targetTableName
     *
     * @return string
     */
    protected function getAssociationTableName($targetTableName)
    {
        $sourceClassName = $this->extendExtension->getEntityClassByTableName('orocrm_task');
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
     * @param string $fieldName
     *
     * @return ParametrizedSqlMigrationQuery
     */
    protected function getDropEntityConfigManyToOneRelationQuery($fieldName)
    {
        $dropFieldIndexSql = 'DELETE FROM oro_entity_config_index_value'
            . ' WHERE entity_id IS NULL AND field_id IN ('
            . ' SELECT oecf.id FROM oro_entity_config_field AS oecf'
            . ' WHERE oecf.field_name = :field'
            . ' AND oecf.entity_id IN ('
            . ' SELECT oec.id'
            . ' FROM oro_entity_config AS oec'
            . ' WHERE oec.class_name = :class'
            . ' ))';
        $dropFieldSql = 'DELETE FROM oro_entity_config_field'
            . ' WHERE field_name = :field'
            . ' AND entity_id IN ('
            . ' SELECT id'
            . ' FROM oro_entity_config'
            . ' WHERE class_name = :class'
            . ' )';

        $taskClassName = $this->extendExtension->getEntityClassByTableName('orocrm_task');

        $query = new ParametrizedSqlMigrationQuery();
        $query->addSql(
            $dropFieldIndexSql,
            ['field' => $fieldName, 'class' => $taskClassName],
            ['field' => 'string', 'class' => 'string']
        );
        $query->addSql(
            $dropFieldSql,
            ['field' => $fieldName, 'class' => $taskClassName],
            ['field' => 'string', 'class' => 'string']
        );

        return $query;
    }
}
