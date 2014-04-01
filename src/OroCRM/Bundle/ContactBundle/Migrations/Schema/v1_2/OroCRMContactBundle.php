<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_contact');
        $table->getColumn('birthday')->setType(Type::getType(Type::DATE));
        $table->addIndex(array('first_name', 'last_name'), 'contact_name_idx');
    }
}
