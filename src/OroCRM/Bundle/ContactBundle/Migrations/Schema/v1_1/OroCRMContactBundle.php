<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactBundle extends Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_contact_to_contact_group', 'orocrm_contact_to_contact_grp')
        );
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_contact_address_to_address_type', 'orocrm_contact_adr_to_adr_type')
        );
    }
}
