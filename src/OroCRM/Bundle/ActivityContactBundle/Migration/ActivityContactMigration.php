<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migration;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\EntityExtendBundle\Migration\Schema\ExtendSchema;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityContactMigration implements Migration
{
    /** @var EntityMetadataHelper */
    protected $metadataHelper;

    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    /**
     * @param EntityMetadataHelper    $metadataHelper
     * @param ActivityContactProvider $activityContactProvider
     */
    public function __construct(EntityMetadataHelper $metadataHelper, ActivityContactProvider $activityContactProvider)
    {
        $this->metadataHelper          = $metadataHelper;
        $this->activityContactProvider = $activityContactProvider;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema instanceof ExtendSchema) {
            $queries->addQuery(
                new ActivityContactMigrationQuery($schema, $this->metadataHelper, $this->activityContactProvider)
            );
        }
    }
}
