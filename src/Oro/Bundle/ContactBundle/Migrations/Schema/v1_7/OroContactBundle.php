<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('orocrm_contact');
        $table->dropIndex('contact_name_idx');
        $table->addIndex(['last_name', 'first_name'], 'contact_name_idx');
    }
}
