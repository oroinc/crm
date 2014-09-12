<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use OroCRM\Bundle\CampaignBundle\Form\EventListener\TransportSettingsListener;

class TransportSettingsListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailTransportProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var TransportSettingsListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->emailTransportProvider = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TransportSettingsListener($this->emailTransportProvider, $this->doctrineHelper);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        ];
        $this->assertEquals($expected, $this->listener->getSubscribedEvents());
    }

    public function testPreSetHasTransport()
    {
        $this->markTestIncomplete('CRM-1974');
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue('test_type'));

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

    }
}
