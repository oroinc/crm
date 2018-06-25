<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Cache;

use Oro\Bundle\MagentoBundle\Cache\WsdlCacheClearer;

class WsdlCacheClearerTest extends \PHPUnit\Framework\TestCase
{
    public function testClear()
    {
        $wsdlManager = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Service\WsdlManager')
            ->disableOriginalConstructor()
            ->getMock();
        $wsdlManager->expects($this->once())
            ->method('clearAllWsdlCaches');

        $clearer = new WsdlCacheClearer($wsdlManager);
        $clearer->clear('.');
    }
}
