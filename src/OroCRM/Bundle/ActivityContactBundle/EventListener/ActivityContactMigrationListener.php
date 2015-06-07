<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;

use OroCRM\Bundle\ActivityContactBundle\Migration\ActivityContactMigration;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityContactMigrationListener
{
    /**  @var EntityMetadataHelper */
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
     * POST UP event handler
     *
     * @param PostMigrationEvent $event
     */
    public function onPostUp(PostMigrationEvent $event)
    {
        $event->addMigration(
            new ActivityContactMigration($this->metadataHelper, $this->activityContactProvider)
        );
    }
}
