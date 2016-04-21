<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AllowNullableFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contact');
        
        $lastNameColumn = $table->getColumn('last_name');
        $lastNameColumn->setNotnull(false);

        $firstNameColumn = $table->getColumn('first_name');
        $firstNameColumn->setNotnull(false);
    }
}
