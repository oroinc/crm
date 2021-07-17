<?php

namespace Oro\Bundle\ActivityContactBundle\EventListener;

use Oro\Bundle\ActivityContactBundle\Migration\ActivityContactMigration;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;

class ActivityContactMigrationListener
{
    /**  @var EntityMetadataHelper */
    protected $metadataHelper;

    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    public function __construct(EntityMetadataHelper $metadataHelper, ActivityContactProvider $activityContactProvider)
    {
        $this->metadataHelper          = $metadataHelper;
        $this->activityContactProvider = $activityContactProvider;
    }

    /**
     * POST UP event handler
     */
    public function onPostUp(PostMigrationEvent $event)
    {
        $event->addMigration(
            new ActivityContactMigration($this->metadataHelper, $this->activityContactProvider)
        );
    }
}
