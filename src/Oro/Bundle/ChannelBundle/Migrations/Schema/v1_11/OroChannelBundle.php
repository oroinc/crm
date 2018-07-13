<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroChannelBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery(new UpdateJsonArrayQuery());
        $queries->addPostQuery(new SetCommentOnJsonArrayQuery());
    }
}
