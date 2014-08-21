<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMAccountBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');

        $table->dropColumn('extend_website');
        $table->dropColumn('extend_employees');
        $table->dropColumn('extend_ownership');
        $table->dropColumn('extend_ticker_symbol');
        $table->dropColumn('extend_rating');
        $table->dropColumn('extend_description');
    }
}
