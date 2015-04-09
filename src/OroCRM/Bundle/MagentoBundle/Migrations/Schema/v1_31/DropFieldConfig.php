<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropFieldConfig implements Migration
{
    /**
     * @var array
     */
    protected $fields = [
        'channel_id' => [
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\CustomerGroup',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Product',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Store',
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Website'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        foreach ($this->fields as $fieldName => $entityClasses) {
            foreach ($entityClasses as $entityClass) {
                $dropFieldsSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;

                $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
                $dropFieldsQuery->addSql(
                    $dropFieldsSql,
                    ['field_name' => $fieldName, 'class_name' => $entityClass],
                    ['field_name' => Type::STRING, 'class_name' => Type::STRING]
                );
                $queries->addPostQuery($dropFieldsQuery);
            }
        }
    }
}
