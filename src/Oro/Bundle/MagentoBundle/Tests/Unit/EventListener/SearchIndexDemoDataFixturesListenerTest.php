<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\MagentoBundle\EventListener\SearchIndexDemoDataFixturesListener;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

class SearchIndexDemoDataFixturesListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $listenerManager;

    /** @var SearchIndexDemoDataFixturesListener */
    protected $listener;

    protected function setUp(): void
    {
        $this->listenerManager = $this->createMock(OptionalListenerManager::class);

        $this->listener = new SearchIndexDemoDataFixturesListener($this->listenerManager);
    }

    public function testOnPreLoadForNotDemoFixtures()
    {
        $event = $this->createMock(MigrationDataFixturesEvent::class);

        $event->expects(self::once())
            ->method('isDemoFixtures')
            ->willReturn(false);
        $this->listenerManager->expects(self::never())
            ->method('disableListener');

        $this->listener->onPreLoad($event);
    }

    public function testOnPreLoadForDemoFixtures()
    {
        $event = $this->createMock(MigrationDataFixturesEvent::class);

        $event->expects(self::once())
            ->method('isDemoFixtures')
            ->willReturn(true);
        $this->listenerManager->expects(self::once())
            ->method('disableListener')
            ->with(SearchIndexDemoDataFixturesListener::SEARCH_INDEX_LISTENER);

        $this->listener->onPreLoad($event);
    }

    public function testOnPostLoadForNotDemoFixtures()
    {
        $event = $this->createMock(MigrationDataFixturesEvent::class);

        $event->expects(self::once())
            ->method('isDemoFixtures')
            ->willReturn(false);
        $this->listenerManager->expects(self::never())
            ->method('enableListener');

        $this->listener->onPostLoad($event);
    }

    public function testOnPostLoadForDemoFixtures()
    {
        $event = $this->createMock(MigrationDataFixturesEvent::class);

        $event->expects(self::once())
            ->method('isDemoFixtures')
            ->willReturn(true);
        $this->listenerManager->expects(self::once())
            ->method('enableListener')
            ->with(SearchIndexDemoDataFixturesListener::SEARCH_INDEX_LISTENER);

        $this->listener->onPostLoad($event);
    }
}
