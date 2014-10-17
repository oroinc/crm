<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_3;

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

/**
 *
 */
class OroCRMCallBundle implements
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
        $queries->addPreQuery($this->getFillUserActivityQuery());
        $queries->addPreQuery($this->getFillAccountActivityQuery());
        $queries->addPreQuery($this->getFillContactActivityQuery());
        $queries->addPreQuery($this->getFillPhoneQuery());

        $callTable = $schema->getTable('orocrm_call');

        // relation with account
        $callTable->removeForeignKey('FK_1FBD1A2411A6570A');
        $callTable->dropColumn('related_account_id');
        $queries->addPostQuery($this->getDropEntityConfigManyToOneRelationQuery('relatedAccount'));

        // relation with contact
        $callTable->removeForeignKey('FK_1FBD1A246D6C2DFA');
        $callTable->dropColumn('related_contact_id');
        $queries->addPostQuery($this->getDropEntityConfigManyToOneRelationQuery('relatedContact'));

        // relation with contact phone
        $callTable->removeForeignKey('FK_1FBD1A24A156BF5C');
        $callTable->dropColumn('contact_phone_id');
        $queries->addPostQuery($this->getDropEntityConfigManyToOneRelationQuery('contactPhoneNumber'));
    }

    /**
     * @return string
     */
    protected function getFillUserActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, user_id)'
            . ' SELECT id, owner_id'
            . ' FROM orocrm_call'
            . ' WHERE owner_id IS NOT NULL';

        return sprintf($sql, $this->getAssociationTableName('oro_user'));
    }

    /**
     * @return string
     */
    protected function getFillAccountActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, account_id)'
            . ' SELECT id, related_account_id'
            . ' FROM orocrm_call'
            . ' WHERE related_account_id IS NOT NULL';

        return sprintf($sql, $this->getAssociationTableName('orocrm_account'));
    }

    /**
     * @return string
     */
    protected function getFillContactActivityQuery()
    {
        $sql = 'INSERT INTO %s (call_id, contact_id)'
            . ' SELECT id, related_contact_id'
            . ' FROM orocrm_call'
            . ' WHERE related_contact_id IS NOT NULL';

        return sprintf($sql, $this->getAssociationTableName('orocrm_contact'));
    }

    /**
     * @return string
     */
    protected function getFillPhoneQuery()
    {
        return
            'UPDATE orocrm_call SET phone_number = ('
            . 'SELECT phone FROM orocrm_contact_phone WHERE id = orocrm_call.contact_phone_id'
            . ') WHERE contact_phone_id IS NOT NULL';
    }

    /**
     * @param string $targetTableName
     *
     * @return string
     */
    protected function getAssociationTableName($targetTableName)
    {
        $sourceClassName = $this->extendExtension->getEntityClassByTableName('orocrm_call');
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

        $callClassName = $this->extendExtension->getEntityClassByTableName('orocrm_call');

        $query = new ParametrizedSqlMigrationQuery();
        $query->addSql(
            $dropFieldIndexSql,
            ['field' => $fieldName, 'class' => $callClassName],
            ['field' => 'string', 'class' => 'string']
        );
        $query->addSql(
            $dropFieldSql,
            ['field' => $fieldName, 'class' => $callClassName],
            ['field' => 'string', 'class' => 'string']
        );

        return $query;
    }
}
