<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Environment;

use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;

class TestEntitiesMigrationListener
{
    /**
     * @param PostMigrationEvent $event
     */
    public function onPostUp(PostMigrationEvent $event)
    {
        $event->addMigration(new TestEntitiesMigration());
    }
}
