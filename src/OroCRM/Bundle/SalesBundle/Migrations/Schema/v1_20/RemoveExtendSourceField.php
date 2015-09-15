<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveExtendSourceField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new RemoveExtendSourceFieldQuery());
    }
}
