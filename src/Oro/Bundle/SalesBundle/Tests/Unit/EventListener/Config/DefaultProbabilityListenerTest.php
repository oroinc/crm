<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\EventListener\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;
use Oro\Bundle\SalesBundle\EventListener\Config\DefaultProbabilityListener;

class DefaultProbabilityListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testLoadConfig(): void
    {
        $oldValue = 'old value';
        $newValue = 'new value';

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects(self::once())
            ->method('getMergedWithParentValue')
            ->with($oldValue, 'key', true)
            ->willReturn($newValue);

        $event = new ConfigGetEvent($configManager, 'key', $oldValue, true, 'scope', 123);
        $listener = new DefaultProbabilityListener();
        $listener->loadConfig($event);

        self::assertEquals($newValue, $event->getValue());
    }
}
