<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateFeatureConfig());

        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM orocrm_channel_entity_name WHERE name IN (:entities)',
                [
                    'entities' => [
                        'Oro\Bundle\SalesBundle\Entity\Lead',
                        'Oro\Bundle\SalesBundle\Entity\Opportunity',
                    ],
                ],
                [
                    'entities' => Connection::PARAM_STR_ARRAY,
                ]
            )
        );
    }
}
