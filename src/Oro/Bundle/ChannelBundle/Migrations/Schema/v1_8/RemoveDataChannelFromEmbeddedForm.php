<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveDataChannelFromEmbeddedForm implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->disableChannelField($queries);
    }

    /**
     * @param QueryBag $queries
     */
    protected function disableChannelField(QueryBag $queries)
    {
        $queries->addPostQuery(new UpdateEntityConfigFieldValueQuery(
            'Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm',
            'dataChannel',
            'form',
            'is_enabled',
            false
        ));
    }
}
