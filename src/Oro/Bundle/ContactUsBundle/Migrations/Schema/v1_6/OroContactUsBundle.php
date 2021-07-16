<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->modifycOrocrmContactusRequestTable($schema);
    }

    protected function modifycOrocrmContactusRequestTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_request');

        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_342872E8BDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_342872E8BDC09B73'
        );
    }
}
