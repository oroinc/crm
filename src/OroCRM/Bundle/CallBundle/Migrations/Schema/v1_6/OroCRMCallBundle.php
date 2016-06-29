<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::createCallDirectionTranslationTable($schema);
        self::createCallStatusTranslationTable($schema);
    }

    /**
     * Generate table orocrm_call_direction_trans
     *
     * @param Schema $schema
     */
    public static function createCallDirectionTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_call_direction_trans **/
        $table = $schema->createTable('orocrm_call_direction_trans');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 32));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'call_direction_translation_idx',
            array()
        );
        /** End of generate table orocrm_call_direction_trans **/
    }

    /**
     * Generate table orocrm_call_status_trans
     *
     * @param Schema $schema
     */
    public static function createCallStatusTranslationTable(Schema $schema)
    {
        /** Generate table orocrm_call_status_trans **/
        $table = $schema->createTable('orocrm_call_status_trans');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('foreign_key', 'string', array('length' => 32));
        $table->addColumn('content', 'string', array('length' => 255));
        $table->addColumn('locale', 'string', array('length' => 8));
        $table->addColumn('object_class', 'string', array('length' => 255));
        $table->addColumn('field', 'string', array('length' => 32));
        $table->setPrimaryKey(array('id'));
        $table->addIndex(
            array('locale', 'object_class', 'field', 'foreign_key'),
            'call_status_translation_idx',
            array()
        );
        /** End of generate table orocrm_call_status_trans **/
    }
}
