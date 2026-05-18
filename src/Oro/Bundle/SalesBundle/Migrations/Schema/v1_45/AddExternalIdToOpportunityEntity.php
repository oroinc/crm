<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_45;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddExternalIdToOpportunityEntity implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        if (!$table->hasColumn('external_id')) {
            $table->addColumn('external_id', 'string', ['length' => 36, 'notnull' => false, OroOptions::KEY => [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                'importexport' => ['excluded' => true],
                'dataaudit' => ['auditable' => true]
            ]]);
            $options = new OroOptions();
            $options->append('extend', 'unique_key.keys', [['name' => 'external_id', 'key' => ['external_id']]]);
            $table->addOption(OroOptions::KEY, $options);
        }
    }
}
