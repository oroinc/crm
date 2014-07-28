<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class OroCRMAccountBundle implements Migration
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_7166D37132C8A3DE', []);
        $table->addForeignKeyConstraint($schema->getTable('oro_organization'), ['organization_id'],
            ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
