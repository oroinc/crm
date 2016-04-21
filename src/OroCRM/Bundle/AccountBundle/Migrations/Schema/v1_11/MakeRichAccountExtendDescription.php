<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MakeRichAccountExtendDescription implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');
        $column = $table->getColumn('extend_description');
        $column->setOptions(
            [
                OroOptions::KEY => [
                    'form' => ['type' => 'oro_resizeable_rich_text'],
                    'view' => ['type' => 'html']
                ]
            ]
        );
    }
}
