<?php

namespace Oro\Bridge\CallCRM\Tests\Unit\Provider;

use Oro\Bridge\CallCRM\Provider\CallDirectionProvider;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\CallBundle\Entity\CallDirection;

class CallDirectionProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CallDirectionProvider */
    protected $provider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $activityManager;

    protected function setUp(): void
    {
        $this->activityManager = $this->getMockBuilder('Oro\Bundle\ActivityBundle\Manager\ActivityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CallDirectionProvider($this->activityManager);
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
