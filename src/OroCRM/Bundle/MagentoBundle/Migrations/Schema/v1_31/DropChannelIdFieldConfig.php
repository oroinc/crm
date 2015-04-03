<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropChannelIdFieldConfig implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $dropFieldsSql = 'DELETE FROM oro_entity_config_field WHERE field_name = :field_name';
        $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
        $dropFieldsQuery->addSql($dropFieldsSql, ['field_name' => 'channel_id'], ['field_name' => 'string']);
        $queries->addPostQuery($dropFieldsQuery);
    }
}
