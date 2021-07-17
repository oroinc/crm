<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class EmbededFormType implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateEmbededFormType($queries);
    }

    public static function updateEmbededFormType(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_embedded_form',
            'form_type',
            'orocrm_',
            'oro_'
        ));
    }
}
