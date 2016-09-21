<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25_3;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

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
        $class = 'OroCRM\Bundle\SalesBundle\Entity\Lead';
        $fields = ['email', 'phone_number'];

        $queries->addPostQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_entity_config_field WHERE field_name IN (:fields)
                  AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class)',
                ['class' => $class, 'fields' => $fields],
                ['class' => Type::STRING, 'fields' => Connection::PARAM_STR_ARRAY]
            )
        );
    }
}
