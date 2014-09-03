<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityBundle\Migrations\MigrateTypeMigration;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactUsBundle extends MigrateTypeMigration implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeType($schema, $queries, 'orocrm_contactus_contact_rsn', 'id', Type::INTEGER);
        $this->changeType($schema, $queries, 'orocrm_contact_group', 'id', Type::INTEGER);
    }
}
