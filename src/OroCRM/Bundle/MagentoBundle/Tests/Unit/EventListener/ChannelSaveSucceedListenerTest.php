<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener\ChannelSaveSucceedListenerTest as BaseTestCase;
use OroCRM\Bundle\MagentoBundle\EventListener\ChannelSaveSucceedListener;

class ChannelSaveSucceedListenerTest extends BaseTestCase
{
    /**
     * @return ChannelSaveSucceedListener
     */
    protected function getListener()
    {
        return new ChannelSaveSucceedListener($this->settingProvider, $this->registry);
    }

    public function assertConnectors()
    {
        $this->assertEquals(
            $this->integration->getConnectors(),
            ['TestConnector1_initial', 'TestConnector2_initial', 'TestConnector1', 'TestConnector2']
        );
    }
}
