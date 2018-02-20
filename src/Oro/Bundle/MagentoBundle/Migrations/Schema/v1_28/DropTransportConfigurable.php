<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropTransportConfigurable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $classNames = [
            'Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport',
            'Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport'
        ];

        foreach ($classNames as $className) {
            $dropFieldsSql = 'DELETE FROM oro_entity_config_field'
                . ' WHERE entity_id IN ('
                . ' SELECT id'
                . ' FROM oro_entity_config'
                . ' WHERE class_name = :class'
                . ' )';
            $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldsQuery->addSql(
                $dropFieldsSql,
                ['class' => $className],
                ['class' => 'string']
            );
            $queries->addPostQuery($dropFieldsQuery);

            $dropConfigurationSql = 'DELETE FROM oro_entity_config WHERE class_name = :class';
            $dropConfigurationQuery = new ParametrizedSqlMigrationQuery();
            $dropConfigurationQuery->addSql(
                $dropConfigurationSql,
                ['class' => $className],
                ['class' => 'string']
            );
            $queries->addQuery($dropConfigurationQuery);
        }
    }
}
