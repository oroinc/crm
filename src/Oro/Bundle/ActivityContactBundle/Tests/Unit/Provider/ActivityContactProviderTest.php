<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestDirectionProvider;
use Oro\Component\Testing\Unit\TestContainerBuilder;

class ActivityContactProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ActivityContactProvider */
    private $provider;

    /** @var TestDirectionProvider */
    private $directionProvider;

    protected function setUp(): void
    {
        $this->directionProvider = new TestDirectionProvider();

        $providers = TestContainerBuilder::create()
            ->add(TestActivity::class, $this->directionProvider)
            ->getContainer($this);

        $this->provider = new ActivityContactProvider(
            [TestActivity::class],
            $providers
        );
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

        $this->assertNull($this->provider->getActivityDate(new \stdClass()));
    }

    public function testGetSupportedActivityClasses()
    {
        $this->assertEquals(
            ['Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity'],
            $this->provider->getSupportedActivityClasses()
        );
    }
}
