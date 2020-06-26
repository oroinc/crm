<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_3;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveOldEntityConfigs implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $class = 'Oro\Bundle\SalesBundle\Entity\Lead';
        $fields = ['email', 'phoneNumber'];

        $queries->addPostQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_entity_config_field WHERE field_name IN (:fields)
                  AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class)',
                ['class' => $class, 'fields' => $fields],
                ['class' => Types::STRING, 'fields' => Connection::PARAM_STR_ARRAY]
            )
        );
    }
}
