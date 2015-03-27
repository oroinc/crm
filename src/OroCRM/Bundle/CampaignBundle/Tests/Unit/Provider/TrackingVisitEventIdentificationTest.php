<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

use OroCRM\Bundle\CampaignBundle\Provider\TrackingVisitEventIdentification;

class TrackingVisitEventIdentificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var TrackingVisitEventIdentification */
    protected $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($this->em);
        $this->provider = new TrackingVisitEventIdentification($doctrine);
    }

    public function testIsApplicable()
    {
        $this->assertFalse($this->provider->isApplicable(new TrackingVisit()));
    }

    public function testGetIdentityTarget()
    {
        $this->assertNull($this->provider->getIdentityTarget());
    }

    public function testGetEventTargets()
    {
        $this->assertEquals(
            [
                'OroCRM\Bundle\CampaignBundle\Entity\Campaign'
            ],
            $this->provider->getEventTargets()
        );
    }

    public function testIsApplicableVisitEvent()
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $event->setWebEvent($webEvent);
        $this->assertFalse($this->provider->isApplicableVisitEvent($event));
        $webEvent->setCode('test');
        $this->assertTrue($this->provider->isApplicableVisitEvent($event));
    }

    /**
     * @dataProvider processData
     * @param $isFind
     */
    public function testProcessEvent($isFind)
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $webEvent->setCode('test');
        $event->setWebEvent($webEvent);

        $testResult = new \stdClass();

        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMCampaignBundle:Campaign')
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn($isFind ? $testResult : null);

        $this->assertEquals($isFind ? [$testResult] : [], $this->provider->processEvent($event));
    }

    public function processData()
    {
        return [
            [true],
            [false]
        ];
    }
}
