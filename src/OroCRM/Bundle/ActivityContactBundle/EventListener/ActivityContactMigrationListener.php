<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;

use OroCRM\Bundle\ActivityContactBundle\Migration\ActivityContactMigration;

class ActivityContactMigrationListener
{
    /**  @var EntityMetadataHelper */
    protected $metadataHelper;

    /**
     * @param EntityMetadataHelper $metadataHelper
     */
    public function __construct(EntityMetadataHelper $metadataHelper)
    {
        $this->metadataHelper = $metadataHelper;
    }

    /**
     * POST UP event handler
     *
     * @param PostMigrationEvent $event
     */
    public function onPostUp(PostMigrationEvent $event)
    {
        $event->addMigration(
            new ActivityContactMigration($this->metadataHelper)
        );
    }
}
