<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactUsBundle implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        // prohibits to enable any activity to ContactRequest entity
        $options = new OroOptions();
        $options->set('activity', 'immutable', true);
        $schema->getTable('orocrm_contactus_request')->addOption(OroOptions::KEY, $options);
    }
}
