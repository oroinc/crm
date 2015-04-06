<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Cache;

use OroCRM\Bundle\MagentoBundle\Cache\WsdlCacheClearer;

class WsdlCacheClearerTest extends \PHPUnit_Framework_TestCase
{
    public function testClear()
    {
        $wsdlManager = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Service\WsdlManager')
            ->disableOriginalConstructor()
            ->getMock();
        $wsdlManager->expects($this->once())
            ->method('clearAllWsdlCaches');

        $clearer = new WsdlCacheClearer($wsdlManager);
        $clearer->clear('.');
    }
}
