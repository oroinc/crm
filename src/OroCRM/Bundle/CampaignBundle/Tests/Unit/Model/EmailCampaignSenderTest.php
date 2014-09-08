<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
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
    protected $transport;

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

        $this->transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');

        $this->sender = new EmailCampaignSender(
            $this->marketingListProvider,
            $this->configManager,
            $this->marketingListConnector,
            $this->contactInformationFieldsProvider
        );
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
            ->setTemplate(new EmailTemplate())
            ->setFromEmail(reset($to));

        $this->marketingListProvider
            ->expects($this->once())
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue($iterable));

        if (!empty($iterable)) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly(sizeof($iterable)))
                ->method('getQueryContactInformationFields')
                ->with(
                    $this->equalTo($segment),
                    $this->isType('object'),
                    $this->equalTo(ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
                )
                ->will($this->returnValue($to));

            $this->marketingListConnector
                ->expects($this->exactly(sizeof($iterable)))
                ->method('contact')
                ->with(
                    $this->equalTo($marketingList),
                    $this->equalTo(self::ENTITY_ID)
                );
        }

        $this->sender->setTransport($this->transport);
        $this->sender->send($campaign);
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        $entity = $this
            ->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
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
