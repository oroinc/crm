<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Environment;

use Oro\Bundle\MigrationBundle\Event\PostMigrationEvent;

class TestEntitiesMigrationListener
{
    public function onPostUp(PostMigrationEvent $event)
    {
        $event->addMigration(new TestEntitiesMigration());
    }
}
