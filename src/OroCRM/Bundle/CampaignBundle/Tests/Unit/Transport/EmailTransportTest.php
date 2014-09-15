<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\InternalTransportSettings;
use OroCRM\Bundle\CampaignBundle\Transport\EmailTransport;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class EmailTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTransport
     */
    protected $transport;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailHelper;

    protected function setUp()
    {
        $this->processor = $this
            ->getMockBuilder('Oro\Bundle\EmailBundle\Mailer\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->renderer = $this
            ->getMockBuilder('Oro\Bundle\EmailBundle\Provider\EmailRenderer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailHelper = $this->getMock('Oro\Bundle\EmailBundle\Tools\EmailAddressHelper');

        $this->transport = new EmailTransport($this->processor, $this->renderer, $this->helper, $this->emailHelper);
    }

    /**
     * @param int    $id
     * @param string $entity
     * @param array  $from
     * @param array  $to
     * @param string $subject
     * @param string $body
     *
     * @dataProvider sendDataProvider
     */
    public function testSend($id, $entity, array $from, array $to, $subject, $body, $expects)
    {
        $emails = array_keys($from);

        $this->helper
            ->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue($id));

        $this->emailHelper
            ->expects($this->once())
            ->method('buildFullEmailAddress')
            ->will($this->returnValue(sprintf('%s <%s>', reset($emails), reset($from))));

        $marketingList = new MarketingList();
        $marketingList->setEntity($entity);

        $template = new EmailTemplate();
        $template->setType('html');
        $settings = new InternalTransportSettings();
        $settings
            ->setTemplate($template);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTransportSettings($settings);

        $this->renderer
            ->expects($this->once())
            ->method('compileMessage')
            ->will($this->returnValue([$subject, $body]));

        $emailModel = new Email();
        $emailModel
            ->setFrom(sprintf('%s <%s>', reset($emails), reset($from)))
            ->setType($template->getType())
            ->setEntityClass($entity)
            ->setEntityId($id)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body);

        $this->processor
            ->expects($expects)
            ->method('process')
            ->with($this->equalTo($emailModel));

        $this->transport->send($campaign, $entity, $from, $to);
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        return [
            [1, '\stdClass', ['sender@example.com' => 'Sender Name'], [], 'subject', 'body', $this->once()],
            [null, '\stdClass', ['sender@example.com' => 'Sender Name'], [], 'subject', 'body', $this->once()],
            ['string', '\stdClass', ['sender@example.com' => 'Sender Name'], [], 'subject', 'body', $this->once()],
            [
                1,
                '\stdClass',
                ['sender@example.com' => 'Sender Name'],
                ['test@example.com'],
                'subject',
                'body',
                $this->once()
            ],
            [
                1,
                '\stdClass',
                ['sender@example.com' => 'Sender Name'],
                ['test@example.com'],
                null,
                'body',
                $this->once()
            ],
            [
                1,
                '\stdClass',
                ['sender@example.com' => 'Sender Name'],
                ['test@example.com'],
                'subject',
                null,
                $this->once()
            ],
            [1, '\stdClass', ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null, $this->once()],
            [1, null, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null, $this->once()],
            [1, '\stdClass', ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null, $this->once()],
            [1, '\stdClass', ['sender@example.com' => 'Sender Name'], [null], null, null, $this->once()],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Sender email and name is empty
     */
    public function testFromEmpty()
    {
        $entity = new \stdClass();

        $this->helper
            ->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue(1));

        $marketingList = new MarketingList();
        $marketingList->setEntity($entity);

        $template = new EmailTemplate();
        $template->setType('html');
        $settings = new InternalTransportSettings();
        $settings
            ->setTemplate($template);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTransportSettings($settings);

        $this->transport->send($campaign, $entity, [], []);
    }
}
