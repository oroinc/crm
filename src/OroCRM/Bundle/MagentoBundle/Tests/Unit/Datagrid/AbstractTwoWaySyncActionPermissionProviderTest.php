<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider;

abstract class AbstractTwoWaySyncActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelSettingsProvider
     */
    protected $settingsProvider;

    protected function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
