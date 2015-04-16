<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\TrackingCustomerIdentification;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity\TestTrackingWebsite;

class TrackingCustomerIdentificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var TrackingCustomerIdentification */
    protected $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $extendConfigProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extendConfigProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($this->em);

        $this->provider = new TrackingCustomerIdentification(
            $doctrine,
            $this->extendConfigProvider,
            $this->settingsProvider
        );
    }

    public function testIsApplicableTrackingWebsiteWithoutChannel()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(false);

        $this->assertFalse($this->provider->isApplicable(new TrackingVisit()));
    }

    public function testIsApplicable()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);

        $visit = new TrackingVisit();
        $channel = new Channel();
        $channel->setChannelType(ChannelType::TYPE);
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $this->assertTrue($this->provider->isApplicable($visit));
    }

    public function testIsApplicableNonMagentoChannel()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);

        $visit = new TrackingVisit();
        $channel = new Channel();
        $channel->setChannelType('testChannel');
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $this->assertFalse($this->provider->isApplicable($visit));
    }

    public function testGetIdentityTarget()
    {
        $expectedResult = '\stdClass';

        $this->settingsProvider->expects($this->once())
            ->method('getCustomerIdentityFromConfig')
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->provider->getIdentityTarget());
    }

    public function testGetEventTargets()
    {
        $this->assertEquals(
            [
                'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                'OroCRM\Bundle\MagentoBundle\Entity\Product',
                'OroCRM\Bundle\MagentoBundle\Entity\Cart'
            ],
            $this->provider->getEventTargets()
        );
    }

    public function testIsApplicableVisitEventTrackingWebsiteWithoutChannel()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(false);

        $this->assertFalse($this->provider->isApplicableVisitEvent(new TrackingVisitEvent()));
    }

    public function testIsApplicableVisitEvent()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);

        $visitEvent = new TrackingVisitEvent();
        $visit = new TrackingVisit();
        $visitEvent->setVisit($visit);
        $channel = new Channel();
        $channel->setChannelType(ChannelType::TYPE);
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $this->assertTrue($this->provider->isApplicableVisitEvent($visitEvent));
    }

    public function testIsApplicableVisitEventNonMagentoChannel()
    {
        $this->extendConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);

        $visitEvent = new TrackingVisitEvent();
        $visit = new TrackingVisit();
        $visitEvent->setVisit($visit);
        $channel = new Channel();
        $channel->setChannelType('test');
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $this->assertFalse($this->provider->isApplicableVisitEvent($visitEvent));
    }

    /**
     * @dataProvider processEventProvider
     *
     * @param $eventType
     * @param $eventValue
     * @param $repoClass
     * @param $parameters
     * @param $channel
     */
    public function testProcessEvent($eventType, $eventValue, $repoClass, $parameters, $channel)
    {
        $trackingEvent = new TrackingVisitEvent();
        $visit = new TrackingVisit();
        $channel->setChannelType(ChannelType::TYPE);
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);
        $trackingDict = new TrackingEventDictionary();
        $trackingDict->setName($eventType);
        $trackingEvent->setEvent($trackingDict);
        $trackingEvent->setWebsite($website);
        $trackingEvent->setVisit($visit);
        $webEvent = new TrackingEvent();
        $webEvent->setValue($eventValue);
        $trackingEvent->setWebEvent($webEvent);

        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with($repoClass)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with($parameters)
            ->willReturn(new \stdClass());

        $this->provider->processEvent($trackingEvent);
    }

    public function processEventProvider()
    {
        $channel = new Channel();
        return [
            [
                'cart item added',
                100,
                'OroCRMMagentoBundle:Product',
                [
                    'originId' => 100
                ],
                $channel
            ],
            [
                'order successfully placed',
                108.8,
                'OroCRMMagentoBundle:Order',
                [
                    'subtotalAmount' => 108.8,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'order placed',
                1000098,
                'OroCRMMagentoBundle:Order',
                [
                    'incrementId' => 1000098,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'user entered checkout',
                45.78,
                'OroCRMMagentoBundle:Cart',
                [
                    'subTotal' => 45.78,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'user logged out',
                123,
                'OroCRMMagentoBundle:Customer',
                [
                    'originId' => 123,
                    'dataChannel' => $channel
                ],
                $channel
            ],
        ];
    }

    public function testIdentifyParsedVisit()
    {
        $visit = new TrackingVisit();
        $visit->setParsedUID(123);
        $channel = new Channel();
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $result = new \stdClass();

        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(
                [
                    'originId'    => 123,
                    'dataChannel' => $channel
                ]
            )
            ->willReturn($result);

        $this->assertEquals(
            [
                'parsedUID'    => 123,
                'targetObject' => $result
            ],
            $this->provider->identify($visit)
        );
    }

    public function testIdentifyGuestVisit()
    {
        $visit = new TrackingVisit();
        $visit->setUserIdentifier('id=guest; visitor-id=87');
        $channel = new Channel();
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $this->em->expects($this->never())
            ->method('getRepository');
        $this->assertNull(
            $this->provider->identify($visit)
        );
    }

    public function testIdentify()
    {
        $visit = new TrackingVisit();
        $visit->setUserIdentifier('id=118; email=test@test.com; visitor-id=89');
        $channel = new Channel();
        $website = new TestTrackingWebsite();
        $website->setChannel($channel);
        $visit->setTrackingWebsite($website);

        $result = new \stdClass();

        $repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(
                [
                    'originId'    => 118,
                    'dataChannel' => $channel
                ]
            )
            ->willReturn($result);

        $this->assertEquals(
            [
                'parsedUID'    => 118,
                'targetObject' => $result
            ],
            $this->provider->identify($visit)
        );
    }
}
