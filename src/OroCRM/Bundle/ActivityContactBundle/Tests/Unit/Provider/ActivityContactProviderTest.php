<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestDirectionProvider;

class ActivityContactProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityContactProvider */
    protected $provider;

    /** @var TestDirectionProvider */
    protected $directionProvider;

    public function setUp()
    {
        $entityManager = $metadataMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider          = new ActivityContactProvider($entityManager);
        $this->directionProvider = new TestDirectionProvider();

        $this->provider->addProvider($this->directionProvider);
    }

    public function testGetActivityDirection()
    {
        $activity = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, new \DateTime());
        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_INCOMING,
            $this->provider->getActivityDirection($activity, new \stdClass())
        );

        $activity = new TestActivity(DirectionProviderInterface::DIRECTION_OUTGOING, new \DateTime());
        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_OUTGOING,
            $this->provider->getActivityDirection($activity, new \stdClass())
        );

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_UNKNOWN,
            $this->provider->getActivityDirection(new \stdClass(), new \stdClass())
        );
    }

    public function testGetActivityDate()
    {
        $date     = new \DateTime('2015-01-01');
        $activity = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, $date);
        $this->assertEquals($date, $this->provider->getActivityDate($activity));

        $this->assertFalse($this->provider->getActivityDate(new \stdClass()));
    }

    public function testGetSupportedActivityClasses()
    {
        $this->assertEquals(
            ['OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity'],
            $this->provider->getSupportedActivityClasses()
        );
    }
}
