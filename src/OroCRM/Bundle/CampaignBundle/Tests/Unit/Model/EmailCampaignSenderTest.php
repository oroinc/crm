<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model;

use Oro\Bundle\SegmentBundle\Entity\Segment;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSender;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class EmailCampaignSenderTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_ID = 1;

    /**
     * @var EmailCampaignSender
     */
    protected $sender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListConnector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transport;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportProvider;

    protected function setUp()
    {
        $this->marketingListProvider = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager = $this
            ->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->marketingListConnector = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contactInformationFieldsProvider = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');

        $this->transportProvider = $this->getMock('OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider');

        $this->sender = new EmailCampaignSender(
            $this->marketingListProvider,
            $this->configManager,
            $this->marketingListConnector,
            $this->contactInformationFieldsProvider,
            $this->registry,
            $this->transportProvider
        );

        $this->sender->setLogger($this->logger);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Transport is required to perform send
     */
    public function testAssertTransport()
    {
        $campaign = new EmailCampaign();

        $this->sender->send($campaign);
    }

    public function testNotSent()
    {
        $segment = new Segment();

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderName('test')
            ->setSenderEmail('test@localhost');

        $this->marketingListProvider
            ->expects($this->once())
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue(null));

        $this->transport->expects($this->never())
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send($campaign);
    }

    /**
     * @param array $iterable
     *
     * @dataProvider sendDataProvider
     */
    public function testSend($iterable, $to)
    {
        $segment = new Segment();

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderName(reset($to))
            ->setSenderEmail(reset($to));

        $itCount = count($iterable);
        $this->marketingListProvider
            ->expects($this->once())
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->exactly($itCount + 1))
            ->method('persist');
        $manager->expects($this->atLeastOnce())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('commit');

        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount))
                ->method('getQueryContactInformationFields')
                ->with(
                    $this->equalTo($segment),
                    $this->isType('object'),
                    $this->equalTo(ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
                )
                ->will($this->returnValue($to));

            $marketingListItem = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem')
                ->disableOriginalConstructor()
                ->getMock();
            $this->marketingListConnector
                ->expects($this->exactly($itCount))
                ->method('contact')
                ->with(
                    $this->equalTo($marketingList),
                    $this->equalTo(self::ENTITY_ID)
                )
                ->will($this->returnValue($marketingListItem));
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send($campaign);
    }

    /**
     * @param array $iterable
     *
     * @dataProvider sendDataProvider
     */
    public function testSendError($iterable, $to)
    {
        $segment = new Segment();

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderEmail(reset($to));

        $itCount = count($iterable);
        $this->marketingListProvider
            ->expects($this->once())
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->once())
            ->method('persist')
            ->with($campaign);
        $manager->expects($this->atLeastOnce())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('rollback');

        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount))
                ->method('getQueryContactInformationFields')
                ->with(
                    $this->equalTo($segment),
                    $this->isType('object'),
                    $this->equalTo(ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
                )
                ->will($this->returnValue($to));

            $this->marketingListConnector
                ->expects($this->exactly($itCount))
                ->method('contact')
                ->with(
                    $this->equalTo($marketingList),
                    $this->equalTo(self::ENTITY_ID)
                )
                ->will(
                    $this->returnCallback(
                        function () {
                            throw new \Exception('Error');
                        }
                    )
                );
            $this->logger->expects($this->exactly($itCount))
                ->method('error');
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send($campaign);
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        $entity = $this->getMockBuilder('\stdClass')
            ->setMethods(array('getId'))
            ->getMock();

        $entity
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue(self::ENTITY_ID));

        return [
            [[$entity, $entity], []],
            [[$entity], []],
            [[], []],
            [[], ['mail@example.com']],
            [[$entity], ['mail@example.com']],
            [[$entity, $entity], ['mail@example.com']],
        ];
    }
}
