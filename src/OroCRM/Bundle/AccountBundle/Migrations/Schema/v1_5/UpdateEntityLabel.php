<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;

class UpdateEntityLabel implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\AccountBundle\Entity\Account',
                'entity',
                'label',
                'orocrm.account.entity_label'
            )
        );
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\AccountBundle\Entity\Account',
                'entity',
                'plural_label',
                'orocrm.account.entity_plural_label'
            )
        );
    }
}
