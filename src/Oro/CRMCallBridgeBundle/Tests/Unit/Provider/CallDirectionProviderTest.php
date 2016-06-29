<?php

namespace Oro\CRMCallBridgeBundle\Tests\Unit\Provider;

use Oro\CRMCallBridgeBundle\Provider\CallDirectionProvider;
use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Entity\CallDirection;

class CallDirectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CallDirectionProvider */
    protected $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $activityManager;

    public function setUp()
    {
        $this->activityManager = $this->getMockBuilder('Oro\Bundle\ActivityBundle\Manager\ActivityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CallDirectionProvider($this->activityManager);
    }

    public function testGetSupportedClass()
    {
        $this->assertEquals('OroCRM\Bundle\CallBundle\Entity\Call', $this->provider->getSupportedClass());
    }

    public function testGetDirection()
    {
        $directionName = 'incoming';

        $direction = new CallDirection($directionName);
        $call      = new Call();
        $call->setDirection($direction);
        $this->assertEquals($directionName, $this->provider->getDirection($call, new \stdClass()));
    }

    public function testGetDate()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($date->format('Y'), $this->provider->getDate(new Call())->format('Y'));
    }
}
