<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactUsBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery('ALTER TABLE orocrm_contactus_request DROP FOREIGN KEY FK_342872E8374A36E9;');

        $table = $schema->getTable('orocrm_contactus_request');
        $table->changeColumn('contact_reason_id', ['type' => Type::getType('integer')]);

        $table = $schema->getTable('orocrm_contactus_contact_rsn');
        $table->changeColumn('id', ['type' => Type::getType('integer')]);
    }
}
