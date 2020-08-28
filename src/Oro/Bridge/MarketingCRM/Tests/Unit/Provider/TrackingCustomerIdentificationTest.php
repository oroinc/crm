<?php

namespace Oro\Bridge\MarketingCRM\Tests\Unit\Provider;

use Oro\Bridge\MarketingCRM\Provider\TrackingCustomerIdentification;
use Oro\Bridge\MarketingCRM\Tests\Unit\Fixtures\Entity\TestTrackingWebsite;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TrackingCustomerIdentificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingCustomerIdentification */
    protected $provider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $em;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $extendConfigProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $settingsProvider;

    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extendConfigProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
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
        $channel->setChannelType(MagentoChannelType::TYPE);
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
                'Oro\Bundle\MagentoBundle\Entity\Order',
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                'Oro\Bundle\MagentoBundle\Entity\Product',
                'Oro\Bundle\MagentoBundle\Entity\Cart'
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
        $channel->setChannelType(MagentoChannelType::TYPE);
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
        $channel->setChannelType(MagentoChannelType::TYPE);
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
                'OroMagentoBundle:Product',
                [
                    'originId' => 100
                ],
                $channel
            ],
            [
                'order successfully placed',
                108.8,
                'OroMagentoBundle:Order',
                [
                    'subtotalAmount' => 108.8,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'order placed',
                1000098,
                'OroMagentoBundle:Order',
                [
                    'incrementId' => 1000098,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'user entered checkout',
                45.78,
                'OroMagentoBundle:Cart',
                [
                    'subTotal' => 45.78,
                    'dataChannel' => $channel
                ],
                $channel
            ],
            [
                'user logged out',
                123,
                'OroMagentoBundle:Customer',
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
