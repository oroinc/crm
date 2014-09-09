<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
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

        $this->transport = new EmailTransport($this->processor, $this->renderer, $this->helper);
    }

    /**
     * @param int    $id
     * @param string $entity
     * @param string $from
     * @param array  $to
     * @param string $subject
     * @param string $body
     *
     * @dataProvider sendDataProvider
     */
    public function testSend($id, $entity, $from, array $to, $subject, $body, $expects)
    {
        $this->helper
            ->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue($id));

        $marketingList = new MarketingList();
        $marketingList->setEntity($entity);

        $template = new EmailTemplate();
        $template->setType('html');
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTemplate($template);

        $this->renderer
            ->expects($this->once())
            ->method('compileMessage')
            ->will($this->returnValue([$subject, $body]));

        $emailModel = new Email();
        $emailModel
            ->setFrom($from)
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
            [1, '\stdClass', 'from', [], 'subject', 'body', $this->once()],
            [null, '\stdClass', 'from', [], 'subject', 'body', $this->once()],
            ['string', '\stdClass', 'from', [], 'subject', 'body', $this->once()],
            [1, '\stdClass', 'from', ['test@example.com'], 'subject', 'body', $this->once()],
            [1, '\stdClass', 'from', ['test@example.com'], null, 'body', $this->once()],
            [1, '\stdClass', 'from', ['test@example.com'], 'subject', null, $this->once()],
            [1, '\stdClass', 'from', ['test@example.com'], null, null, $this->once()],
            [1, null, 'from', ['test@example.com'], null, null, $this->once()],
            [1, '\stdClass', null, ['test@example.com'], null, null, $this->once()],
            [1, '\stdClass', null, [null], null, null, $this->once()],
        ];
    }
}
