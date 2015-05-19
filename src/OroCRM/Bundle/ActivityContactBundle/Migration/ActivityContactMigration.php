<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migration;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\EntityExtendBundle\Migration\Schema\ExtendSchema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class ActivityContactMigration implements Migration
{
    /** @var EntityMetadataHelper */
    protected $metadataHelper;

    /**
     * @param EntityMetadataHelper $metadataHelper
     */
    public function __construct(EntityMetadataHelper $metadataHelper)
    {
        $this->metadataHelper = $metadataHelper;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema instanceof ExtendSchema) {
            $queries->addQuery(
                new ActivityContactMigrationQuery($schema, $this->metadataHelper)
            );
        }
    }
}
