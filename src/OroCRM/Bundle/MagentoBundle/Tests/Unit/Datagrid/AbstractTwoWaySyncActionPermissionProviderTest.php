<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

abstract class AbstractTwoWaySyncActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param bool $isTwoWaySyncEnabled
     *
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getChannel($isTwoWaySyncEnabled = false)
    {
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');

        $settings = [];
        if (null !== $isTwoWaySyncEnabled) {
            $settings['isTwoWaySyncEnabled'] = $isTwoWaySyncEnabled;
        }

        $settings = Object::create($settings);
        $channel->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($settings));

        return $channel;
    }
}
