<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Provider;

use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Entity\CallDirection;
use OroCRM\Bundle\CallBundle\Provider\CallDirectionProvider;

class CallDirectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CallDirectionProvider */
    protected $provider;

    public function setUp()
    {
        $this->provider = new CallDirectionProvider();
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
